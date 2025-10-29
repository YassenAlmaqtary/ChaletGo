import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:get/get.dart';

import '../../../core/constants/app_colors.dart';
import '../controllers/chalet_detail_controller.dart';
import '../../../routes/app_pages.dart';

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
        decoration: const BoxDecoration(gradient: AppColors.backgroundGradient),
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
                      decoration: const BoxDecoration(
                        color: AppColors.surface,
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
                              color: AppColors.softBlue,
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
                            decoration: const BoxDecoration(
                              gradient: AppColors.cardGradient,
                              borderRadius:
                                  BorderRadius.all(Radius.circular(20)),
                            ),
                            child: Row(
                              children: [
                                Container(
                                  padding: const EdgeInsets.all(14),
                                  decoration: const BoxDecoration(
                                    shape: BoxShape.circle,
                                    gradient: AppColors.accentGradient,
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
                                            .bodySmall
                                            ?.copyWith(
                                                color: AppColors.muted
                                                    .withOpacity(0.7)),
                                      ),
                                      const SizedBox(height: 4),
                                      Text(
                                        '${chalet.pricePerNight.toStringAsFixed(0)} ر.ي',
                                        style: Theme.of(context)
                                            .textTheme
                                            .titleMedium
                                            ?.copyWith(
                                              fontWeight: FontWeight.w700,
                                              color: AppColors.dark,
                                            ),
                                      ),
                                    ],
                                  ),
                                ),
                                ElevatedButton(
                                  onPressed: () {
                                    Get.toNamed(
                                      Routes.bookingStart,
                                      arguments: chalet,
                                    );
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
          decoration: const BoxDecoration(
            gradient: AppColors.cardGradient,
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
            style: Theme.of(context)
                .textTheme
                .bodySmall
                ?.copyWith(color: AppColors.muted),
            overflow: TextOverflow.ellipsis,
          ),
        ),
      ],
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
        color: AppColors.softBlue,
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
            style: Theme.of(context)
                .textTheme
                .bodySmall
                ?.copyWith(color: AppColors.muted.withOpacity(0.8)),
          ),
          const SizedBox(height: 2),
          Text(
            value,
            style: Theme.of(context)
                .textTheme
                .titleMedium
                ?.copyWith(fontWeight: FontWeight.w700),
          ),
        ],
      ),
    );
  }
}
