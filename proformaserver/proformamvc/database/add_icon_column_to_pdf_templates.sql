-- Add icon column to pdf_templates
ALTER TABLE pdf_templates ADD COLUMN icon VARCHAR(50) DEFAULT 'ph-paint-brush' AFTER color_brand;

-- Update existing defaults
UPDATE pdf_templates SET icon = 'ph-star' WHERE nombre LIKE '%orange%';
UPDATE pdf_templates SET icon = 'ph-buildings' WHERE nombre LIKE '%blue%';
UPDATE pdf_templates SET icon = 'ph-printer' WHERE nombre LIKE '%simple%';
