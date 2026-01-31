<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Hash;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('حذف'),
            Actions\Action::make('toggle_active')
                ->label(fn () => $this->record->is_active ? 'حظر' : 'إلغاء الحظر')
                ->icon(fn () => $this->record->is_active ? 'heroicon-o-lock-closed' : 'heroicon-o-lock-open')
                ->color(fn () => $this->record->is_active ? 'danger' : 'success')
                ->requiresConfirmation()
                ->modalHeading(fn () => $this->record->is_active ? 'حظر المستخدم' : 'إلغاء حظر المستخدم')
                ->modalDescription(fn () => $this->record->is_active 
                    ? 'سيتم منع المستخدم من تسجيل الدخول. هل أنت متأكد؟'
                    : 'سيتم السماح للمستخدم بتسجيل الدخول مرة أخرى. هل أنت متأكد؟')
                ->action(function () {
                    $this->record->update(['is_active' => !$this->record->is_active]);
                    $this->refreshFormData(['is_active']);
                }),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // إذا تم تغيير كلمة المرور، قم بتشفيرها
        if (isset($data['password']) && filled($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            // إذا لم يتم تغيير كلمة المرور، احذفها من البيانات
            unset($data['password']);
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
