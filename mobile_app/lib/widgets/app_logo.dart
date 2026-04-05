import 'package:flutter/material.dart';

/// App branding: uses [assets/1.jpeg] (add the file under `mobile_app/assets/`).
class AppLogo extends StatelessWidget {
  const AppLogo({super.key});

  static const String _assetPath = 'assets/1.jpeg';

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return ClipRRect(
      borderRadius: BorderRadius.circular(20),
      child: Image.asset(
        _assetPath,
        width: 140,
        height: 140,
        fit: BoxFit.contain,
        errorBuilder: (context, error, stackTrace) {
          return Container(
            width: 140,
            height: 140,
            alignment: Alignment.center,
            decoration: BoxDecoration(
              color: theme.colorScheme.surfaceContainerHighest,
              borderRadius: BorderRadius.circular(20),
            ),
            child: Icon(
              Icons.image_not_supported_outlined,
              size: 48,
              color: theme.colorScheme.outline,
            ),
          );
        },
      ),
    );
  }
}
