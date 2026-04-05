import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:get/get.dart';

import '../../../core/constants/app_colors.dart';
import '../../../core/utils/amenity_icon_data.dart';
import '../../../data/models/chalet_amenity_model.dart';
import '../controllers/chalet_detail_controller.dart';
import '../../../routes/app_pages.dart';
import '../../../core/services/auth_service.dart';

class ChaletDetailView extends StatelessWidget {
  const ChaletDetailView({super.key});

  @override
  Widget build(BuildContext context) {
    final controller = Get.find<ChaletDetailController>();

    return Scaffold(
      extendBodyBehindAppBar: true,
      appBar: AppBar(
        title: const Text('تفاصيل الشاليه'),
        backgroundColor: Colors.transparent,
      ),
      body: Container(
        decoration: BoxDecoration(gradient: AppColors.getBackgroundGradient(context)),
        child: Obx(() {
          if (controller.errorMessage.value.isNotEmpty &&
              controller.chalet.value == null) {
            return Center(child: Text(controller.errorMessage.value));
          }

          if (controller.chalet.value == null) {
            return const Center(child: CircularProgressIndicator());
          }

          final chalet = controller.chalet.value!;

          return RefreshIndicator(
            onRefresh: () async {
              final slug = controller.chaletSlug ?? chalet.slug;
              if (slug.isNotEmpty) {
                await controller.loadChalet(slug);
              }
            },
            child: SingleChildScrollView(
              physics: const AlwaysScrollableScrollPhysics(),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  _ImagesCarousel(controller: controller),
                  Transform.translate(
                    offset: const Offset(0, -30),
                    child: Container(
                      width: double.infinity,
                      decoration: BoxDecoration(
                        color: Theme.of(context).cardColor,
                        borderRadius:
                            BorderRadius.vertical(top: Radius.circular(32)),
                        boxShadow: [
                          BoxShadow(
                            color: Color(0x14000000),
                            blurRadius: 30,
                            offset: Offset(0, -6),
                          ),
                        ],
                      ),
                      padding: const EdgeInsets.fromLTRB(24, 36, 24, 32),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Container(
                            width: 42,
                            height: 4,
                            margin: const EdgeInsets.only(bottom: 20),
                            decoration: BoxDecoration(
                              color: Theme.of(context).brightness == Brightness.dark
                                  ? const Color(0xFF2E2E2E)
                                  : AppColors.softBlue,
                              borderRadius: BorderRadius.circular(2),
                            ),
                          ),
                          Text(
                            chalet.name,
                            style: Theme.of(context).textTheme.titleLarge,
                          ),
                          const SizedBox(height: 10),
                          Row(
                            children: [
                              _IconText(
                                icon: Icons.location_on_outlined,
                                text: chalet.location,
                              ),
                              const SizedBox(width: 16),
                              _IconText(
                                icon: Icons.star_rounded,
                                text:
                                    '${chalet.rating.toStringAsFixed(1)} (${chalet.totalReviews})',
                                iconColor: AppColors.accent,
                              ),
                            ],
                          ),
                          const SizedBox(height: 20),
                          Wrap(
                            spacing: 10,
                            runSpacing: 10,
                            children: [
                              _InfoBadge(
                                icon: Icons.group,
                                label: 'الضيوف',
                                value: '${chalet.maxGuests}',
                              ),
                              _InfoBadge(
                                icon: Icons.bed_outlined,
                                label: 'الغرف',
                                value: '${chalet.bedrooms}',
                              ),
                              _InfoBadge(
                                icon: Icons.bathtub_outlined,
                                label: 'الحمامات',
                                value: '${chalet.bathrooms}',
                              ),
                            ],
                          ),
                          const SizedBox(height: 24),
                          Container(
                            padding: const EdgeInsets.all(18),
                            decoration: BoxDecoration(
                              gradient: AppColors.getCardGradient(context),
                              borderRadius:
                                  const BorderRadius.all(Radius.circular(20)),
                            ),
                            child: Row(
                              children: [
                                Container(
                                  padding: const EdgeInsets.all(14),
                                  decoration: BoxDecoration(
                                    shape: BoxShape.circle,
                                    gradient: AppColors.getAccentGradient(context),
                                  ),
                                  child: const Icon(Icons.payments,
                                      color: Colors.white),
                                ),
                                const SizedBox(width: 18),
                                Expanded(
                                  child: Column(
                                    crossAxisAlignment:
                                        CrossAxisAlignment.start,
                                    children: [
                                      Text(
                                        'سعر الليلة',
                                        style: Theme.of(context)
                                            .textTheme
                                            .bodySmall,
                                      ),
                                      const SizedBox(height: 4),
                                      Text(
                                        '${chalet.pricePerNight.toStringAsFixed(0)} ر.ي',
                                        style: Theme.of(context)
                                            .textTheme
                                            .titleMedium
                                            ?.copyWith(
                                              fontWeight: FontWeight.w700,
                                            ),
                                      ),
                                    ],
                                  ),
                                ),
                                ElevatedButton(
                                  onPressed: () {
                                    final authService = Get.find<AuthService>();
                                    // Check if user is logged in
                                    if (!authService.isLoggedIn) {
                                      // Save the booking start route
                                      authService.setPendingRoute(Routes.bookingStart);
                                      // Navigate to login
                                      Get.toNamed(Routes.login, arguments: chalet);
                                    } else {
                                      // User is logged in, proceed to booking
                                      Get.toNamed(
                                        Routes.bookingStart,
                                        arguments: chalet,
                                      );
                                    }
                                  },
                                  style: ElevatedButton.styleFrom(
                                    padding: const EdgeInsets.symmetric(
                                        horizontal: 22, vertical: 14),
                                    shape: RoundedRectangleBorder(
                                      borderRadius: BorderRadius.circular(14),
                                    ),
                                  ),
                                  child: const Text('احجز الآن'),
                                ),
                              ],
                            ),
                          ),
                          const SizedBox(height: 24),
                          Text(
                            'عن الشاليه',
                            style: Theme.of(context).textTheme.titleMedium,
                          ),
                          const SizedBox(height: 10),
                          Text(
                            chalet.description.isNotEmpty
                                ? chalet.description
                                : 'لا توجد تفاصيل لهذا الشاليه حالياً',
                            style: Theme.of(context)
                                .textTheme
                                .bodyMedium
                                ?.copyWith(height: 1.6),
                          ),
                          const SizedBox(height: 24),
                          Text(
                            'المرافق',
                            style: Theme.of(context).textTheme.titleMedium,
                          ),
                          const SizedBox(height: 12),
                          if (chalet.amenities.isEmpty)
                            Text(
                              'لا توجد مرافق مسجلة لهذا الشاليه',
                              style: Theme.of(context)
                                  .textTheme
                                  .bodyMedium
                                  ?.copyWith(height: 1.6),
                            )
                          else
                            LayoutBuilder(
                              builder: (context, constraints) {
                                final w = constraints.maxWidth;
                                final columns = w >= 400 ? 3 : 2;
                                const spacing = 10.0;
                                final tileW = (w - spacing * (columns - 1)) /
                                    columns;
                                final sorted = [...chalet.amenities]..sort(
                                    (a, b) =>
                                        a.name.compareTo(b.name),
                                  );
                                return Wrap(
                                  spacing: spacing,
                                  runSpacing: spacing,
                                  children: sorted
                                      .map(
                                        (a) => SizedBox(
                                          width: tileW,
                                          child: _AmenityTile(amenity: a),
                                        ),
                                      )
                                      .toList(),
                                );
                              },
                            ),
                        ],
                      ),
                    ),
                  ),
                ],
              ),
            ),
          );
        }),
      ),
    );
  }
}

class _ImagesCarousel extends StatelessWidget {
  const _ImagesCarousel({required this.controller});

  final ChaletDetailController controller;

  @override
  Widget build(BuildContext context) {
    return Obx(() {
      final chalet = controller.chalet.value;
      final images = chalet?.images ?? [];

      if (images.isEmpty) {
        return Container(
          height: 320,
          decoration: BoxDecoration(
            gradient: AppColors.getCardGradient(context),
            borderRadius: BorderRadius.vertical(bottom: Radius.circular(36)),
          ),
          alignment: Alignment.center,
          child: const Icon(Icons.image, size: 60, color: AppColors.primary),
        );
      }

      return Stack(
        alignment: Alignment.bottomCenter,
        children: [
          SizedBox(
            height: 360,
            child: ClipRRect(
              borderRadius:
                  const BorderRadius.vertical(bottom: Radius.circular(36)),
              child: PageView.builder(
                itemCount: images.length,
                onPageChanged: controller.onImageChanged,
                itemBuilder: (context, index) {
                  final imageUrl = images[index];
                  Widget image = CachedNetworkImage(
                    imageUrl: imageUrl,
                    fit: BoxFit.cover,
                    width: double.infinity,
                    placeholder: (context, url) => Container(
                      color: Colors.grey.shade200,
                      alignment: Alignment.center,
                      child: const CircularProgressIndicator(),
                    ),
                    errorWidget: (context, url, error) => Container(
                      color: Colors.grey.shade200,
                      alignment: Alignment.center,
                      child: const Icon(Icons.broken_image, size: 48),
                    ),
                  );

                  if (index == 0 && chalet != null) {
                    image = Hero(
                      tag: 'chalet-image-${chalet.id}',
                      child: image,
                    );
                  }

                  return image;
                },
              ),
            ),
          ),
          if (images.length > 1)
            Positioned(
              bottom: 12,
              child: Obx(() {
                return Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: List.generate(images.length, (index) {
                    final isActive =
                        controller.currentImageIndex.value == index;
                    return AnimatedContainer(
                      duration: const Duration(milliseconds: 200),
                      margin: const EdgeInsets.symmetric(horizontal: 3),
                      width: isActive ? 12 : 8,
                      height: 8,
                      decoration: BoxDecoration(
                        color: isActive
                            ? Colors.white
                            : Colors.white.withOpacity(0.6),
                        borderRadius: BorderRadius.circular(4),
                      ),
                    );
                  }),
                );
              }),
            ),
        ],
      );
    });
  }
}

class _IconText extends StatelessWidget {
  const _IconText({
    required this.icon,
    required this.text,
    this.iconColor,
  });

  final IconData icon;
  final String text;
  final Color? iconColor;

  @override
  Widget build(BuildContext context) {
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Icon(icon, size: 18, color: iconColor ?? AppColors.primary),
        const SizedBox(width: 6),
        Flexible(
          child: Text(
            text,
            style: Theme.of(context).textTheme.bodySmall,
            overflow: TextOverflow.ellipsis,
          ),
        ),
      ],
    );
  }
}

class _AmenityTile extends StatelessWidget {
  const _AmenityTile({required this.amenity});

  final ChaletAmenityModel amenity;

  @override
  Widget build(BuildContext context) {
    final icon = amenityIconFromApiClass(amenity.icon);
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 14),
      decoration: BoxDecoration(
        color: Theme.of(context).brightness == Brightness.dark
            ? const Color(0xFF2E2E2E)
            : AppColors.softBlue,
        borderRadius: BorderRadius.circular(16),
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 22, color: AppColors.primary),
          const SizedBox(height: 8),
          Text(
            amenity.name,
            textAlign: TextAlign.center,
            maxLines: 2,
            overflow: TextOverflow.ellipsis,
            style: Theme.of(context).textTheme.bodySmall?.copyWith(
                  height: 1.25,
                  fontWeight: FontWeight.w600,
                ),
          ),
        ],
      ),
    );
  }
}

class _InfoBadge extends StatelessWidget {
  const _InfoBadge({
    required this.icon,
    required this.label,
    required this.value,
  });

  final IconData icon;
  final String label;
  final String value;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
      decoration: BoxDecoration(
        color: Theme.of(context).brightness == Brightness.dark
            ? const Color(0xFF2E2E2E)
            : AppColors.softBlue,
        borderRadius: BorderRadius.circular(16),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 20, color: AppColors.primary),
          const SizedBox(height: 6),
          Text(
            label,
            style: Theme.of(context).textTheme.bodySmall,
          ),
          const SizedBox(height: 2),
          Text(
            value,
            style: Theme.of(context).textTheme.titleMedium?.copyWith(
                  fontWeight: FontWeight.w700,
                ),
          ),
        ],
      ),
    );
  }
}
