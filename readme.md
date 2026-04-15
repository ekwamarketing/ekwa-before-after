# EKWA Before After Gallery

A beautiful, modern, and highly customizable before-and-after gallery plugin for WordPress. Perfect for dental, medical, beauty, fitness, and any business that wants to showcase transformation results.

## Live Demos

- **Full Gallery view**: [ekwa-testbench.info/plugins/ekwa-before-after/](https://ekwa-testbench.info/plugins/ekwa-before-after/)
- **Category Carousel**: [ekwa-testbench.info/plugins/ekwa-before-after/dental-implant/](https://ekwa-testbench.info/plugins/ekwa-before-after/dental-implant/)

---

## Features

- **Two Display Modes**: Full filterable gallery (`[ekwa_gallery]`) and compact category carousel (`[ekwa_category_carousel]`)
- **Multiple Card Designs**: Stacked, Side-by-Side, Overlay, and Minimal layouts
- **Multi-Level Filtering**: Hierarchical categories and subcategories with clickable filter tabs
- **Multiple Image Sets per Case**: Add multiple before/after angle pairs per case
- **Flexible Image Upload**: Upload before and after as two separate images, or as one combined side-by-side image
- **Modal Lightbox**: Full-screen lightbox with case title, description, thumbnail strip, and prev/next navigation
- **Category Carousel**: Swipeable carousel with responsive breakpoints, autoplay, arrows, and dot indicators
- **Auto-detect Category**: Carousel can automatically match the page slug to a category — no shortcode attribute needed
- **Shortcode Builder**: Visual shortcode builder in the admin settings generates the correct shortcode for you
- **Full Color Control**: Customize background, card, text, accent, hover, and border colors with a live preview
- **Before/After Labels**: Toggle "Before" / "After" overlay labels on images
- **Image Watermarking**: Apply text or image watermarks with configurable position, opacity, size, color, and padding
- **Bulk Watermark Tools**: Apply or remove watermarks across all cases from the Tools tab
- **Settings Import/Export**: Export your settings as JSON and import them on another site
- **Auto-Updates**: Pulls plugin updates directly from GitHub — no manual zip uploads needed
- **Responsive Design**: Fully mobile-friendly across all screen sizes
- **Lightweight & Fast**: No jQuery dependencies on the frontend; scripts load only on pages that use the shortcodes

---

## Installation

1. Upload the plugin folder to `/wp-content/plugins/`
2. Activate the plugin through the **Plugins** menu in WordPress
3. Go to **BA Gallery** in the admin sidebar to add cases and categories
4. Add a shortcode to any page or post (see Shortcode Usage below)

---

## Shortcode Usage

### Full Gallery

Displays all cases in a filterable grid.

```
[ekwa_gallery]
```

| Attribute | Default | Description |
|---|---|---|
| `category` | *(empty)* | Filter by category slug. Leave empty to show all. |
| `limit` | `-1` | Maximum number of cases to display. `-1` = all. |
| `columns` | `3` | Number of columns in the grid (overridden by the Cards Per Row setting). |
| `show_filter` | `yes` | Show or hide the category filter tabs (`yes` / `no`). |

**Examples:**

```
[ekwa_gallery category="cosmetic" limit="12"]
[ekwa_gallery show_filter="no"]
```

---

### Category Carousel

Displays cases in a horizontal swipeable carousel.

```
[ekwa_category_carousel]
```

| Attribute | Default | Description |
|---|---|---|
| `category` | *(auto-detect)* | Category slug to display. Use `all` to show all cases with filter tabs. Leave empty to auto-detect from the page slug. |
| `limit` | `-1` | Maximum number of cases to load. `-1` = all. |
| `per_page` | *(from settings)* | Number of slides visible on desktop. |
| `per_page_tablet` | *(from settings)* | Number of slides visible on tablet. |
| `per_page_mobile` | *(from settings)* | Number of slides visible on mobile. |
| `show_arrows` | *(from settings)* | Show prev/next arrows (`yes` / `no`). |
| `show_dots` | *(from settings)* | Show dot indicators (`yes` / `no`). |
| `autoplay` | *(from settings)* | Enable autoplay (`yes` / `no`). |
| `title` | *(from settings)* | Override the carousel heading text. |

**Examples:**

```
[ekwa_category_carousel category="dental-implant"]
[ekwa_category_carousel category="all" per_page="3" per_page_tablet="2" per_page_mobile="1" show_arrows="yes" autoplay="yes"]
[ekwa_category_carousel limit="5" title="Our Latest Results"]
```

> **Tip:** Use the **Shortcode Builder** inside **BA Gallery → Settings → Carousel** to visually generate the correct shortcode without typing attributes manually.

---

## Admin Settings

Navigate to **BA Gallery → Settings** to configure the plugin. Settings are organized into five tabs:

### Colors Tab

Customize every color in the gallery using a color picker with a live preview panel.

| Setting | Default | Description |
|---|---|---|
| Background Color | `#f5f3f0` | Main gallery section background |
| Card Background | `#ffffff` | Individual case card background |
| Primary Text | `#1a1a1a` | Case titles and main text |
| Secondary Text | `#777777` | Descriptions and subtle labels |
| Accent Color | `#c9a87c` | Buttons, active filter tabs |
| Accent Hover | `#b08d5b` | Hover state for accent elements |
| Border Color | `#e8e4df` | Card borders and dividers |

A **Reset to Defaults** button restores all colors to the original values.

---

### Gallery Tab

| Setting | Options | Description |
|---|---|---|
| Card Design | Stacked / Side by Side / Overlay / Minimal | Controls how before/after images are shown on cards |
| Cards Per Row | 1–6 | Number of cards per row in the gallery grid |
| Upload Mode | Single combined image / Separate images | When enabled, one image containing both before and after (side-by-side) is uploaded per set instead of two separate images |
| Show Before/After Labels | On / Off | Toggles the "Before" and "After" overlay labels on images in cards and the modal |

**Card Design options:**
- **Stacked** — Before image on top, After image below (default)
- **Side by Side** — Before and After shown horizontally next to each other
- **Overlay** — Single image card; hovering reveals the After image
- **Minimal** — Clean single image card with no overlay effect

---

### Carousel Tab

#### Shortcode Builder
A visual builder that lets you pick category, limits, responsive slides per view, arrows, dots, and autoplay — then copies the generated shortcode to your clipboard.

#### Default Carousel Settings

| Setting | Default | Description |
|---|---|---|
| Desktop slides per view | `3` | Applies when viewport ≥ 1025px |
| Tablet slides per view | `2` | Applies when viewport is 601px–1024px |
| Mobile slides per view | `1` | Applies when viewport ≤ 600px |
| Show Arrows | On | Display previous/next navigation arrows |
| Show Dots | On | Display dot pagination indicators |
| Autoplay | Off | Automatically advance slides |
| Autoplay Speed | `5000ms` | Time between auto-advances (1000–15000ms) |
| Show Title | On | Display a heading above the carousel |
| Title Text | `Before & After Results` | The heading text shown above the carousel |
| Custom Card Template | Off | Enable a custom HTML/Twig-style template for carousel cards |

---

### Watermark Tab

| Setting | Options | Description |
|---|---|---|
| Enable Watermark | On / Off | Activate watermarking on all gallery images |
| Watermark Type | Text / Image | Use text or an uploaded image as the watermark |
| Watermark Text | *(text)* | The text to stamp on images (when type = text) |
| Watermark Image | *(media library)* | Select an image from the media library (when type = image) |
| Position | Top-Left / Top-Center / Top-Right / Center / Bottom-Left / Bottom-Center / Bottom-Right | Where the watermark appears on each image |
| Opacity | 0–100 | Watermark transparency percentage |
| Size | *(pixels)* | Font size (text) or image size (image watermark) |
| Color | `#ffffff` | Watermark text color |
| Padding | *(pixels)* | Distance from the edge of the image |

Requires either the **GD Library** or **Imagick** PHP extension. The settings page shows which library is active on your server.

---

### Tools Tab

| Tool | Description |
|---|---|
| Bulk Apply Watermarks | Apply the configured watermark to all existing gallery images at once |
| Remove All Watermarks | Strip watermarks from all gallery images and restore the originals |
| Test Watermark | Apply watermark to a single test image to preview the result before bulk applying |
| Clear & Re-apply | Remove existing watermarks and re-apply the current settings in one step |
| Export Settings | Download all plugin settings as a `.json` file |
| Import Settings | Upload a previously exported `.json` file to restore settings |

---

## Managing Cases

1. Go to **BA Gallery → Add New Case**
2. Enter a **title** and optional **description** (content area)
3. Assign one or more **categories** from the Category panel
4. In the **Image Sets** meta box, click **Add Another Image Set** to add before/after pairs
   - In **separate image mode**: upload a Before image and an After image per set
   - In **single combined image mode**: upload one image containing both
5. Reorder image sets by dragging the handle
6. Click **Publish** or **Update**

---

## Categories

Categories are hierarchical. You can create top-level categories (e.g., *Cosmetic*, *Restorative*) and subcategories under them (e.g., *Veneers*, *Implants*).

Default categories created on activation:
- Cosmetic → Whitening, Veneers
- Restorative → Crowns, Implants, Dentures
- Orthodontic → Clear Aligners, Braces

Manage categories at **BA Gallery → Categories**.

---

## Frequently Asked Questions

**Q: How do I display the gallery on a page?**  
A: Add `[ekwa_gallery]` to the page content using the block editor (Shortcode block) or the classic editor. For a category-specific carousel, use `[ekwa_category_carousel category="your-category-slug"]`.

**Q: What is the difference between `[ekwa_gallery]` and `[ekwa_category_carousel]`?**  
A: `[ekwa_gallery]` displays a full filterable grid with all cases and category tabs. `[ekwa_category_carousel]` displays a compact horizontal carousel, ideal for embedding inside service or treatment pages.

**Q: The carousel shows no results. Why?**  
A: The carousel auto-detects the category from the page slug if no `category` attribute is set. Make sure your page slug matches a category slug exactly, or set the category explicitly: `[ekwa_category_carousel category="dental-implant"]`.

**Q: How do I show all cases in the carousel with category filter tabs?**  
A: Use `category="all"`: `[ekwa_category_carousel category="all"]`.

**Q: Can I have multiple carousels on the same page?**  
A: Yes. Each `[ekwa_category_carousel]` instance is independent with its own data and settings.

**Q: How do I change the accent color to match my brand?**  
A: Go to **BA Gallery → Settings → Colors** and pick your accent color. The live preview updates in real time before you save.

**Q: What card designs are available?**  
A: Four options: **Stacked** (before on top, after below), **Side by Side** (horizontal), **Overlay** (hover to reveal after), and **Minimal** (single image).

**Q: Can I upload one image that already has before and after side by side?**  
A: Yes. Go to **BA Gallery → Settings → Gallery** and enable **Upload Before/After as Single Combined Image**. Image set fields will then show a single upload field.

**Q: How do watermarks work?**  
A: Configure watermark settings under **BA Gallery → Settings → Watermark**, then use the **Tools** tab to apply watermarks to all existing images. New images added after enabling watermarks are watermarked automatically on save.

**Q: Watermarks are not working. What do I check?**  
A: Your server needs either the **GD Library** or **Imagick** PHP extension enabled. The settings page shows which (if any) is available. Contact your host to enable one if neither is shown.

**Q: How do I move settings from one site to another?**  
A: Use **BA Gallery → Settings → Tools → Export Settings** to download a JSON file, then import it on the destination site using **Import Settings**.

**Q: How does the auto-update work?**  
A: The plugin checks for new releases on the [GitHub repository](https://github.com/ekwamarketing/ekwa-before-after) and shows update notifications in the WordPress dashboard just like any other plugin.

**Q: Can I use a custom card template for the carousel?**  
A: Yes. In **BA Gallery → Settings → Carousel**, enable **Custom Card Template** and enter your HTML template. This lets you fully control the carousel card markup.

---

## Screenshots

1. Frontend gallery grid with category filter tabs
2. Modal lightbox with before/after images, thumbnail strip, and navigation
3. Category carousel with arrows and dots
4. Admin case editor with multiple image sets
5. Settings — Colors tab with live preview
6. Settings — Carousel tab with Shortcode Builder
7. Settings — Watermark tab

---

## License

GPLv2 or later

---

## Issues & Suggestions

Submit issues or feature requests: [GitHub Issues](https://github.com/ekwamarketing/ekwa-before-after/issues)

For more information or to contribute, visit the [GitHub repository](https://github.com/ekwamarketing/ekwa-before-after).
