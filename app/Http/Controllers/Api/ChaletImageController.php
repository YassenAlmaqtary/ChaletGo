<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponseTrait;
use App\Models\Chalet;
use App\Models\ChaletImage;
use App\Services\SecureFileUploadService;
use App\Rules\SecureInput;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class ChaletImageController extends Controller
{
    use ApiResponseTrait;

    /**
     * Upload images for a chalet
     */
    public function store(Request $request, string $chaletSlug): JsonResponse
    {
        $user = Auth::guard('api')->user();

        $chalet = Chalet::where('slug', $chaletSlug)->first();

        if (!$chalet) {
            return $this->notFoundResponse('الشاليه غير موجود');
        }

        // Check if user owns this chalet
        if ($chalet->owner_id !== $user->id && $user->user_type !== 'admin') {
            return $this->forbiddenResponse('ليس لديك صلاحية لإضافة صور لهذا الشاليه');
        }

        $validator = Validator::make($request->all(), [
            'images' => 'required|array|min:1|max:10',
            'images.*' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120', // 5MB max
            'alt_texts' => 'nullable|array',
            'alt_texts.*' => ['nullable', 'string', 'max:255', new SecureInput()],
            'is_primary' => 'nullable|integer|min:0', // Index of primary image
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $uploadedImages = [];
        $manager = new ImageManager(new Driver());

        try {
            foreach ($request->file('images') as $index => $image) {
                // Generate unique filename
                $filename = time() . '_' . $index . '_' . uniqid() . '.' . $image->getClientOriginalExtension();

                // Create directory if not exists
                $directory = 'chalets/' . $chalet->id;
                if (!Storage::disk('public')->exists($directory)) {
                    Storage::disk('public')->makeDirectory($directory);
                }

                // Process and resize image
                $processedImage = $manager->read($image->getPathname());

                // Resize to max 1920x1080 while maintaining aspect ratio
                $processedImage->scaleDown(width: 1920, height: 1080);

                // Save original
                $originalPath = $directory . '/' . $filename;
                Storage::disk('public')->put($originalPath, $processedImage->encode());

                // Create thumbnail (400x300)
                $thumbnailPath = $directory . '/thumb_' . $filename;
                $thumbnail = $processedImage->cover(400, 300);
                Storage::disk('public')->put($thumbnailPath, $thumbnail->encode());

                // Get next sort order
                $sortOrder = $chalet->images()->max('sort_order') + 1;

                // Create database record
                $chaletImage = ChaletImage::create([
                    'chalet_id' => $chalet->id,
                    'image_path' => $originalPath,
                    'alt_text' => $request->alt_texts[$index] ?? $chalet->name . ' - صورة ' . ($index + 1),
                    'is_primary' => false,
                    'sort_order' => $sortOrder,
                ]);

                $uploadedImages[] = [
                    'id' => $chaletImage->id,
                    'url' => $chaletImage->image_url,
                    'thumbnail_url' => Storage::url($thumbnailPath),
                    'alt_text' => $chaletImage->alt_text,
                    'sort_order' => $chaletImage->sort_order,
                ];
            }

            // Set primary image if specified
            if ($request->has('is_primary') && isset($uploadedImages[$request->is_primary])) {
                // Remove primary from all images
                $chalet->images()->update(['is_primary' => false]);

                // Set new primary
                $primaryImageId = $uploadedImages[$request->is_primary]['id'];
                ChaletImage::find($primaryImageId)->update(['is_primary' => true]);
                $uploadedImages[$request->is_primary]['is_primary'] = true;
            } elseif ($chalet->images()->where('is_primary', true)->count() === 0) {
                // Set first image as primary if no primary exists
                if (!empty($uploadedImages)) {
                    ChaletImage::find($uploadedImages[0]['id'])->update(['is_primary' => true]);
                    $uploadedImages[0]['is_primary'] = true;
                }
            }

            return $this->successResponse([
                'uploaded_images' => $uploadedImages,
                'total_images' => $chalet->images()->count(),
            ], 'تم رفع الصور بنجاح');

        } catch (\Exception $e) {
            // Clean up uploaded files on error
            foreach ($uploadedImages as $image) {
                if (isset($image['id'])) {
                    $chaletImage = ChaletImage::find($image['id']);
                    if ($chaletImage) {
                        Storage::disk('public')->delete($chaletImage->image_path);
                        $chaletImage->delete();
                    }
                }
            }

            return $this->errorResponse('حدث خطأ أثناء رفع الصور: ' . $e->getMessage());
        }
    }

    /**
     * Update image details
     */
    public function update(Request $request, string $chaletSlug, int $imageId): JsonResponse
    {
        $user = Auth::guard('api')->user();

        $chalet = Chalet::where('slug', $chaletSlug)->first();

        if (!$chalet) {
            return $this->notFoundResponse('الشاليه غير موجود');
        }

        // Check if user owns this chalet
        if ($chalet->owner_id !== $user->id && $user->user_type !== 'admin') {
            return $this->forbiddenResponse('ليس لديك صلاحية لتعديل صور هذا الشاليه');
        }

        $image = $chalet->images()->find($imageId);

        if (!$image) {
            return $this->notFoundResponse('الصورة غير موجودة');
        }

        $validator = Validator::make($request->all(), [
            'alt_text' => 'nullable|string|max:255',
            'is_primary' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $updateData = $request->only(['alt_text', 'sort_order']);

        if ($request->has('is_primary') && $request->is_primary) {
            // Remove primary from all other images
            $chalet->images()->where('id', '!=', $imageId)->update(['is_primary' => false]);
            $updateData['is_primary'] = true;
        }

        $image->update($updateData);

        return $this->successResponse([
            'id' => $image->id,
            'url' => $image->image_url,
            'alt_text' => $image->alt_text,
            'is_primary' => $image->is_primary,
            'sort_order' => $image->sort_order,
        ], 'تم تحديث الصورة بنجاح');
    }

    /**
     * Delete an image
     */
    public function destroy(string $chaletSlug, int $imageId): JsonResponse
    {
        $user = Auth::guard('api')->user();

        $chalet = Chalet::where('slug', $chaletSlug)->first();

        if (!$chalet) {
            return $this->notFoundResponse('الشاليه غير موجود');
        }

        // Check if user owns this chalet
        if ($chalet->owner_id !== $user->id && $user->user_type !== 'admin') {
            return $this->forbiddenResponse('ليس لديك صلاحية لحذف صور هذا الشاليه');
        }

        $image = $chalet->images()->find($imageId);

        if (!$image) {
            return $this->notFoundResponse('الصورة غير موجودة');
        }

        // Don't allow deleting the last image
        if ($chalet->images()->count() <= 1) {
            return $this->errorResponse('لا يمكن حذف آخر صورة للشاليه');
        }

        try {
            // Delete file from storage
            Storage::disk('public')->delete($image->image_path);

            // Delete thumbnail if exists
            $thumbnailPath = str_replace(basename($image->image_path), 'thumb_' . basename($image->image_path), $image->image_path);
            Storage::disk('public')->delete($thumbnailPath);

            $wasPrimary = $image->is_primary;

            // Delete database record
            $image->delete();

            // If deleted image was primary, set another image as primary
            if ($wasPrimary) {
                $newPrimary = $chalet->images()->orderBy('sort_order')->first();
                if ($newPrimary) {
                    $newPrimary->update(['is_primary' => true]);
                }
            }

            return $this->successResponse(null, 'تم حذف الصورة بنجاح');

        } catch (\Exception $e) {
            return $this->errorResponse('حدث خطأ أثناء حذف الصورة');
        }
    }

    /**
     * Reorder images
     */
    public function reorder(Request $request, string $chaletSlug): JsonResponse
    {
        $user = Auth::guard('api')->user();

        $chalet = Chalet::where('slug', $chaletSlug)->first();

        if (!$chalet) {
            return $this->notFoundResponse('الشاليه غير موجود');
        }

        // Check if user owns this chalet
        if ($chalet->owner_id !== $user->id && $user->user_type !== 'admin') {
            return $this->forbiddenResponse('ليس لديك صلاحية لإعادة ترتيب صور هذا الشاليه');
        }

        $validator = Validator::make($request->all(), [
            'image_ids' => 'required|array',
            'image_ids.*' => 'required|integer|exists:chalet_images,id',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            foreach ($request->image_ids as $index => $imageId) {
                $chalet->images()->where('id', $imageId)->update(['sort_order' => $index + 1]);
            }

            $reorderedImages = $chalet->images()->orderBy('sort_order')->get()->map(function ($image) {
                return [
                    'id' => $image->id,
                    'url' => $image->image_url,
                    'alt_text' => $image->alt_text,
                    'is_primary' => $image->is_primary,
                    'sort_order' => $image->sort_order,
                ];
            });

            return $this->successResponse([
                'images' => $reorderedImages
            ], 'تم إعادة ترتيب الصور بنجاح');

        } catch (\Exception $e) {
            return $this->errorResponse('حدث خطأ أثناء إعادة ترتيب الصور');
        }
    }
}
