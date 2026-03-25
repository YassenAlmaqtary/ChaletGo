import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:get/get.dart';

import '../../../core/constants/app_colors.dart';
import '../../../data/models/chalet_model.dart';
import '../../../routes/app_pages.dart';
import '../controllers/chalet_controller.dart';

class ChaletListView extends StatelessWidget {
  final GlobalKey<ScaffoldState>? scaffoldKey;
  
  const ChaletListView({super.key, this.scaffoldKey});

  @override
  Widget build(BuildContext context) {
    final controller = Get.find<ChaletController>();

    return Scaffold(
      extendBodyBehindAppBar: true,
      appBar: AppBar(
        title: Text('chalets'.tr),
        leading: IconButton(
          icon: const Icon(Icons.menu_rounded),
          onPressed: () => scaffoldKey?.currentState?.openDrawer(),
        ),
      ),
      body: Container(
        decoration: BoxDecoration(gradient: AppColors.getBackgroundGradient(context)),
        child: SafeArea(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Padding(
                padding:
                    const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text('discover_destination'.tr,
                        style: Theme.of(context).textTheme.titleLarge),
                    const SizedBox(height: 6),
                    Text('luxury_accommodations'.tr,
                        style: Theme.of(context).textTheme.bodySmall?.copyWith(
                            color: AppColors.muted.withOpacity(0.8))),
                  ],
                ),
              ),
              Expanded(
                child: Obx(() {
                  if (controller.isLoading.value && controller.chalets.isEmpty) {
                    return const Center(child: CircularProgressIndicator());
                  }

                  if (controller.errorMessage.value.isNotEmpty &&
                      controller.chalets.isEmpty) {
                    return RefreshIndicator(
                      onRefresh: controller.fetchChalets,
                      child: SingleChildScrollView(
                        physics: const AlwaysScrollableScrollPhysics(),
                        child: SizedBox(
                          height: MediaQuery.of(context).size.height * 0.6,
                          child: Center(
                            child: Column(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                Text(controller.errorMessage.value),
                                const SizedBox(height: 16),
                                ElevatedButton(
                                  onPressed: controller.fetchChalets,
                                  child: Text('retry'.tr),
                                ),
                              ],
                            ),
                          ),
                        ),
                      ),
                    );
                  }

                  if (controller.chalets.isEmpty) {
                    return RefreshIndicator(
                      onRefresh: controller.fetchChalets,
                      child: SingleChildScrollView(
                        physics: const AlwaysScrollableScrollPhysics(),
                        child: SizedBox(
                          height: MediaQuery.of(context).size.height * 0.6,
                          child: Center(
                            child: Text('no_chalets'.tr),
                          ),
                        ),
                      ),
                    );
                  }

                  return RefreshIndicator(
                    onRefresh: controller.fetchChalets,
                    child: ListView.separated(
                      physics: const AlwaysScrollableScrollPhysics(),
                      padding: const EdgeInsets.symmetric(
                          horizontal: 24, vertical: 12),
                      itemCount: controller.chalets.length,
                      separatorBuilder: (_, __) => const SizedBox(height: 18),
                      itemBuilder: (context, index) {
                        final chalet = controller.chalets[index];
                        return _ChaletCard(
                          chalet: chalet,
                          onTap: () => Get.toNamed(
                            Routes.chaletDetail,
                            arguments: chalet,
                          ),
                        );
                      },
                    ),
                  );
                }),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _ChaletCard extends StatelessWidget {
  const _ChaletCard({required this.chalet, required this.onTap});

  final ChaletModel chalet;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final image = chalet.images.isNotEmpty ? chalet.images.first : null;

    return GestureDetector(
      onTap: onTap,
      child: Container(
        decoration: BoxDecoration(
          gradient: AppColors.getCardGradient(context),
          borderRadius: BorderRadius.circular(24),
          boxShadow: [
            BoxShadow(
              color: AppColors.primary.withOpacity(0.08),
              blurRadius: 18,
              offset: const Offset(0, 12),
            ),
          ],
        ),
        padding: const EdgeInsets.all(18),
        child: Stack(
          children: [
            Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Hero(
                  tag: 'chalet-image-${chalet.id}',
                  child: ClipRRect(
                    borderRadius: BorderRadius.circular(18),
                    child: image != null
                        ? CachedNetworkImage(
                            imageUrl: image,
                            width: 110,
                            height: 110,
                            fit: BoxFit.cover,
                          )
                        : Container(
                            width: 110,
                            height: 110,
                            color: Theme.of(context).brightness == Brightness.dark
                                ? const Color(0xFF2E2E2E).withOpacity(0.7)
                                : Colors.white.withOpacity(0.7),
                            alignment: Alignment.center,
                            child: const Icon(Icons.image_outlined,
                                size: 36, color: AppColors.primary),
                          ),
                  ),
                ),
                const SizedBox(width: 18),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        chalet.name,
                        style: theme.textTheme.titleMedium?.copyWith(
                          fontWeight: FontWeight.w700,
                        ),
                      ),
                      const SizedBox(height: 6),
                      Row(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Icon(Icons.location_on_outlined,
                              size: 16, color: AppColors.primary),
                          const SizedBox(width: 4),
                          Expanded(
                            child: Text(
                              chalet.location,
                              style: theme.textTheme.bodySmall,
                              maxLines: 2,
                              overflow: TextOverflow.ellipsis,
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 12),
                      Row(
                        children: [
                          _InfoChip(
                            icon: Icons.group,
                            label: '${chalet.maxGuests} ${'guests'.tr}',
                          ),
                          const SizedBox(width: 8),
                          _InfoChip(
                            icon: Icons.bed_outlined,
                            label: '${chalet.bedrooms} ${'rooms'.tr}',
                          ),
                        ],
                      ),
                      const SizedBox(height: 14),
                      Text(
                        '${chalet.pricePerNight.toStringAsFixed(0)} ${'price_per_night'.tr}',
                        style: theme.textTheme.titleMedium?.copyWith(
                          fontWeight: FontWeight.w700,
                          color: Theme.of(context).textTheme.titleMedium?.color ?? AppColors.dark,
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
            Positioned(
              top: 0,
              right: 0,
              child: Container(
                padding:
                    const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                decoration: BoxDecoration(
                  color: AppColors.accent.withOpacity(0.95),
                  borderRadius: BorderRadius.circular(14),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    const Icon(Icons.star, color: Colors.white, size: 16),
                    const SizedBox(width: 4),
                    Text(
                      chalet.rating.toStringAsFixed(1),
                      style: theme.textTheme.bodySmall?.copyWith(
                        color: Colors.white,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _InfoChip extends StatelessWidget {
  const _InfoChip({required this.icon, required this.label});

  final IconData icon;
  final String label;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
      decoration: BoxDecoration(
        color: Theme.of(context).brightness == Brightness.dark
            ? const Color(0xFF2E2E2E).withOpacity(0.85)
            : Colors.white.withOpacity(0.85),
        borderRadius: BorderRadius.circular(14),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 16, color: AppColors.primary),
          const SizedBox(width: 4),
          Text(
            label,
            style: Theme.of(context).textTheme.bodySmall?.copyWith(
                  fontWeight: FontWeight.w600,
                ),
          ),
        ],
      ),
    );
  }
}

class _AppBarIconButton extends StatelessWidget {
  const _AppBarIconButton({
    required this.icon,
    required this.onTap,
    this.tooltip,
  });

  final IconData icon;
  final VoidCallback onTap;
  final String? tooltip;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 8),
      child: Tooltip(
        message: tooltip ?? '',
        child: InkWell(
          onTap: onTap,
          borderRadius: BorderRadius.circular(14),
          child: Container(
            width: 40,
            height: 40,
            decoration: BoxDecoration(
              color: Theme.of(context).brightness == Brightness.dark
                  ? const Color(0xFF2E2E2E).withOpacity(0.9)
                  : Colors.white.withOpacity(0.9),
              borderRadius: BorderRadius.circular(14),
              boxShadow: [
                BoxShadow(
                  color: AppColors.primary.withOpacity(0.12),
                  blurRadius: 10,
                  offset: const Offset(0, 6),
                ),
              ],
            ),
            child: Icon(icon, color: AppColors.primary),
          ),
        ),
      ),
    );
  }
}
