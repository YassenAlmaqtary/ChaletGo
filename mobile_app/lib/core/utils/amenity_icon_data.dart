import 'package:flutter/material.dart';

/// Maps API Font Awesome classes (e.g. `fas fa-wifi`) to Material icons for a consistent look.
IconData amenityIconFromApiClass(String? faClass) {
  final key = _extractFaKey(faClass);
  if (key == null) return Icons.widgets_outlined;
  return _faKeyToMaterial[key] ?? Icons.check_circle_outline;
}

String? _extractFaKey(String? raw) {
  if (raw == null || raw.trim().isEmpty) return null;
  for (final part in raw.trim().toLowerCase().split(RegExp(r'\s+'))) {
    if (part.startsWith('fa-')) return part.substring(3);
  }
  return null;
}

/// Keys are the `fa-*` suffix from the API (e.g. `wifi` from `fas fa-wifi`).
const Map<String, IconData> _faKeyToMaterial = {
  'wifi': Icons.wifi,
  'snowflake': Icons.ac_unit,
  'tv': Icons.tv_outlined,
  'utensils': Icons.restaurant_outlined,
  'tshirt': Icons.checkroom_outlined,
  'swimming-pool': Icons.pool,
  'swimmer': Icons.pool,
  'pool': Icons.pool,
  'tree': Icons.park_outlined,
  'fire': Icons.local_fire_department_outlined,
  'car': Icons.directions_car_outlined,
  'chair': Icons.chair_outlined,
  'gamepad': Icons.sports_esports_outlined,
  'circle': Icons.sports_bar_outlined,
  'table-tennis': Icons.sports_tennis_outlined,
  'video': Icons.videocam_outlined,
  'shield-alt': Icons.security,
  'fire-extinguisher': Icons.fire_extinguisher_outlined,
  'hot-tub': Icons.hot_tub_outlined,
  'thermometer-half': Icons.thermostat_outlined,
};
