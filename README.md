# 📧 Atomic Newsletter for Elementor

> Elementor Atomic Forms দিয়ে subscriber email capture করুন — কোনো third-party service ছাড়াই, সম্পূর্ণ আপনার নিজের server-এ।

![WordPress](https://img.shields.io/badge/WordPress-6.0%2B-21759B?style=flat-square&logo=wordpress)
![PHP](https://img.shields.io/badge/PHP-7.4%2B-777BB4?style=flat-square&logo=php)
![License](https://img.shields.io/badge/License-GPLv2%2B-green?style=flat-square)
![Version](https://img.shields.io/badge/Stable-1.0.3-blue?style=flat-square)
![Tested](https://img.shields.io/badge/Tested%20up%20to-WP%206.9-success?style=flat-square)

---

## 🌟 সংক্ষিপ্ত পরিচয়

**Atomic Newsletter for Elementor** একটি lightweight WordPress plugin যা Elementor Atomic Forms-এর মাধ্যমে submit হওয়া email address গুলো আপনার WordPress database-এ সংরক্ষণ করে।

- ✅ **কোনো paid license লাগবে না** — Elementor Pro অথবা বিনামূল্যে [Pro Elements](https://proelements.org/) দিয়ে কাজ করে
- ✅ **কোনো third-party service নেই** — সব data আপনার নিজের server-এ থাকে
- ✅ **কোনো API key নেই** — install করুন এবং ব্যবহার শুরু করুন

---

## ✨ Features

| Feature | বিবরণ |
|---|---|
| 📥 Auto Email Capture | Elementor Atomic Forms থেকে স্বয়ংক্রিয়ভাবে email সংগ্রহ |
| 📋 Admin Dashboard | সুন্দর ও পরিচ্ছন্ন subscriber list |
| 🔍 Search & Filter | email দিয়ে subscriber খুঁজুন |
| 📄 Pagination | ৩০টি করে row — যেকোনো size-এর list-এ smooth |
| 🗑️ Bulk Delete | একটি, নির্বাচিত, বা সব subscriber মুছুন |
| 📤 CSV Export | Excel, Google Sheets-এ compatible CSV download |
| 🔁 Duplicate Prevention | একই email দুইবার store হবে না |
| 🌐 Multisite Support | প্রতিটি site-এর আলাদা subscriber table |
| 🔌 Zero Dependencies | কোনো বাইরের library বা API নেই |

---

## 🔧 Compatibility

এই plugin-টি নিচের যেকোনোটির সাথে কাজ করে:

- **Elementor Pro** (version 4.0+, Atomic Form সহ)
- **[Pro Elements](https://proelements.org/)** *(বিনামূল্যে)* — Elementor Pro-এর open-source বিকল্প

> ⚠️ Classic Elementor Form widget-এর সাথে কাজ করে না। শুধুমাত্র **Atomic Form** widget সমর্থিত।

---

## 📋 Requirements

- **WordPress:** 6.0 বা তার উপরে
- **PHP:** 7.4 বা তার উপরে
- **Elementor:** 4.0+ (Pro অথবা Pro Elements)

---

## 🚀 Installation

**১.** WordPress Plugin Directory থেকে **Elementor** install ও activate করুন।

**২.** [Pro Elements](https://proelements.org/) অথবা Elementor Pro install করুন (Atomic Form-এর জন্য)।

**৩.** এই plugin-টি `/wp-content/plugins/atomic-newsletter-for-elementor/` ফোল্ডারে upload করুন, অথবা WordPress-এ সরাসরি install করুন।

**৪.** WordPress-এর **Plugins** স্ক্রিন থেকে plugin activate করুন।

**৫.** WordPress admin-এ **Subscribers** মেনুতে গিয়ে সংগৃহীত email দেখুন ও export করুন।

---

## ❓ Frequently Asked Questions

<details>
<summary><strong>Elementor Pro কি কিনতে হবে?</strong></summary>

না। এই plugin বিনামূল্যে [Pro Elements](https://proelements.org/) plugin-এর সাথেও কাজ করে, যেটি Elementor Pro-এর সব feature (Atomic Forms সহ) বিনামূল্যে দেয়।
</details>

<details>
<summary><strong>Subscriber data কোথায় সংরক্ষিত হয়?</strong></summary>

সব data আপনার নিজের WordPress database-এ সংরক্ষিত হয়। কোনো external server-এ কিছু পাঠানো হয় না।
</details>

<details>
<summary><strong>CSV export কীভাবে করব?</strong></summary>

WordPress admin-এ **Subscribers** পেজে গিয়ে "Export CSV" বাটনে ক্লিক করুন। Excel ও Google Sheets-এ সরাসরি খোলা যাবে।
</details>

<details>
<summary><strong>Duplicate email কি store হয়?</strong></summary>

না। database-এ email column-এ UNIQUE constraint আছে, তাই একই email দ্বিতীয়বার submit হলে সেটি silently ignore করা হয়।
</details>

<details>
<summary><strong>GDPR compliant কি?</strong></summary>

এই plugin শুধুমাত্র সেই email addresses store করে যেগুলো users স্বেচ্ছায় আপনার form-এ submit করে। Consent language যোগ করা ও privacy policy maintain করা আপনার দায়িত্ব।
</details>

<details>
<summary><strong>WordPress Multisite-এ কাজ করে?</strong></summary>

হ্যাঁ। প্রতিটি site-এর জন্য আলাদা subscriber table তৈরি হয়।
</details>

<details>
<summary><strong>Plugin delete করলে data কী হবে?</strong></summary>

Plugin-এ একটি uninstall routine আছে যা plugin delete করার সময় subscriber database table ও সব plugin option সরিয়ে দেয়।
</details>

<details>
<summary><strong>License key লাগে?</strong></summary>

না। সব feature সম্পূর্ণ বিনামূল্যে, কোনো license key ছাড়াই।
</details>

---

## 📸 Screenshots

1. **Subscriber List** — search, pagination ও delete option সহ সংগৃহীত email-এর তালিকা।
2. **CSV Export** — এক ক্লিকে পুরো subscriber list download করুন।

---

## 📝 Changelog

### 1.0.3
- ✅ Free **Pro Elements** plugin-এর সাথে সম্পূর্ণ compatibility
- ⚡ Vendor dependency সরিয়ে plugin dependency-free করা হয়েছে
- ⚡ CSV export এখন 3,000-row chunk-এ — যেকোনো বড় list-এ safe
- ⚡ Bulk delete এখন 500-ID batch-এ — MySQL packet limit এড়ানো হয়
- 🔧 DB version check এখন `$GLOBALS['wpdb']` ব্যবহার করে
- 🏗️ সব admin component plugin-loaded hook-এ deferred
- 🏗️ Email extraction pipeline চারটি dedicated method-এ বিভক্ত
- 🌐 WordPress Multisite-এ per-site table prefix সমর্থন

### 1.0.2
- ❌ Google Sheets export feature সরানো হয়েছে
- ♻️ সব vendor dependency সরানো — plugin এখন dependency-free
- ✅ Admin Import button এখন সরাসরি CSV download করে

### 1.0.1
- 🔒 Publicly accessible debug panel সরানো হয়েছে
- 🔒 সব admin action-এ nonce verification যোগ করা হয়েছে
- 🔒 Elementor-এর নিজস্ব nonce এখন প্রতিটি request-এ verify হয়
- 📁 Directory listing থেকে সুরক্ষার জন্য সব directory-তে `index.php` যোগ
- 📁 Plugin deletion-এ `uninstall.php` সব table ও option cleanup করে
- 🗄️ Email column-এ UNIQUE constraint যোগ করা হয়েছে
- 🔧 Simplified email validation ও duplicate email prevention fix

### 1.0.0
- 🎉 Initial release
- 📥 Elementor Atomic Forms থেকে email capture
- 👥 Subscriber management dashboard
- 📤 CSV export feature

---

## ⬆️ Upgrade Notice

### 1.0.3
Free Pro Elements compatibility, major performance improvements, এবং Multisite support যোগ হয়েছে। **সকল user-দের জন্য recommended।**

---

## 📄 License

[GPLv2 or later](https://www.gnu.org/licenses/gpl-2.0.html)

---

<p align="center">
  Made with ❤️ by <a href="https://github.com/MONGSING">MONGSING</a>
</p>
