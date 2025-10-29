import 'package:flutter/material.dart';

class AppLogo extends StatelessWidget {
  const AppLogo({super.key});

  @override
  Widget build(BuildContext context) {
    return const Column(
      mainAxisSize: MainAxisSize.min,
      children: [
        FlutterLogo(size: 72),
        SizedBox(height: 12),
        Text(
          'ChaletGo',
          style: TextStyle(fontSize: 24, fontWeight: FontWeight.w600),
        ),
      ],
    );
  }
}
