# coretax-efaktur-pdf-renamer

A lightweight web-based tool for automatically renaming **e-Faktur** (Indonesian tax invoice) PDFs based on their internal **Referensi** values.

---

## 🧾 What It Does

- Accepts a `.zip` file containing one or more **PDF invoices**
- Extracts each PDF and reads its content
- Finds the `Referensi` value (from formats like `(Referensi: YOUR-REFERENCE)`)
- Renames each PDF using the extracted value
- Re-packages renamed PDFs into a downloadable ZIP
- **All files are processed temporarily and deleted automatically after download — so don’t forget to download.**

---

## ✅ Use Cases

- Standardize naming of PDF invoices from **coretax** exports
- Make document management easier for **e-Faktur submissions**
- Simplify email/file sharing by having meaningful filenames

---

## 🛠 Requirements

- PHP 8.0 or higher
- Composer (for dependency management)
- Enabled PHP extensions:
  - `zip`
  - `mbstring`
  - `json`
  - `fileinfo`

---

## 🚀 Setup Instructions

1. **Clone the repo**

   ```bash
   git clone https://github.com/akhalidyahya/coretax-efaktur-pdf-renamer.git
   cd coretax-efaktur-pdf-renamer
