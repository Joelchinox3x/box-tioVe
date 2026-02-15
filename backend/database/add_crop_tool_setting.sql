INSERT INTO system_settings (setting_key, setting_value, description)
VALUES ('crop_tool_version', 'v2', 'Version del editor de recortes: legacy (ImageCropper original) o v2 (EditarImagenCard Skia)')
ON DUPLICATE KEY UPDATE setting_value = 'v2';
