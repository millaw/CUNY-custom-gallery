
# CUNY Gallery Plugin

**CUNY Gallery** is a custom WordPress plugin that allows administrators to create, manage, and display image galleries with flexible layout options (Slider, Lazy Load Gallery, or Slider-Gallery combo). It’s fully integrated with the WordPress Media Library and offers a clean admin interface for editing alt text, image visibility, and gallery styles.

---

## 🎯 Features

- ✅ Create galleries and assign them to pages/posts
- 🖼 Add images directly from the WordPress Media Library
- ✏️ Edit image alt text inline
- 🔄 Drag & drop image reordering
- 👁 Show/Hide individual images
- ❌ Delete images or entire galleries
- 📋 Modal interface with 3-dot menu for image controls
- 🧩 Frontend display using:
  - Lazy load grid
  - Slider
  - Slider + Gallery hybrid
- 🔌 Shortcode system: `[cuny_gallery_123]` auto-generates for each gallery
- 🎨 Custom styling, no Bootstrap dependency

---

## 🚀 Installation

1. Download the plugin or clone this repository:
   ```bash
   git clone https://github.com/yourusername/cuny-custom-gallery.git
   ```

2. Copy the folder to your WordPress plugins directory:
   ```
   /wp-content/plugins/cuny-gallery/
   ```

3. Activate the plugin in the WordPress admin dashboard under **Plugins > Installed Plugins**

---

## 🛠 Usage

### 1. Create a Gallery
- Navigate to **CUNY Gallery** in the WP Admin.
- Choose a gallery name and style.
- Save to generate a unique shortcode like: `[cuny_gallery_5]`

### 2. Add Images
- Edit a gallery.
- Click “Select Image” and pick/upload from the media library.
- Reorder using drag-and-drop.
- Click the **3-dot menu** on an image to:
  - Edit Alt Text
  - Show/Hide
  - Delete

### 3. Display the Gallery
- Copy the shortcode (e.g., `[cuny_gallery_5]`) into any page/post content.

---

## 📦 Shortcodes

| Format                  | Description                      |
|-------------------------|----------------------------------|
| `[cuny_gallery_5]`      | Automatically rewrites to `[cuny_gallery id="5"]` |
| `[cuny_gallery id="5"]` | Used internally after rewrite    |

---

## 🧩 Customization

- Admin styles are in: `admin/assets/admin.css`
- Frontend styles: `frontend/assets/gallery-slider.css`
- JavaScript logic: `admin/assets/admin.js` and `frontend/assets/gallery-slider.js`
- You can modify modal UI, animations, or gallery layout as needed.

---

## ⚙️ Development

To ensure your CSS/JS changes are always loaded fresh:

```php
wp_enqueue_script('your-script', plugin_dir_url(__FILE__) . 'your.js', [], time(), true);
wp_enqueue_style('your-style', plugin_dir_url(__FILE__) . 'your.css', [], time());
```

This disables caching during development.

---

## 📜 License

This plugin is released under the [MIT License](https://opensource.org/licenses/MIT).

---

## ✨ Author

Developed by [Milla Wynn](https://github.com/yourusername) – WordPress Developer at CUNY Central.

---
