# wpDataTables Nationality Support

Adds a nationality field to user profiles and a wrapper shortcode that passes the chosen nationality to wpDataTables through **%VAR1%**. This makes it easy to select data views like **publish_user_view_USA** or **publish_user_view_UK** automatically, based on who is viewing the page.

---

## Features

* **User-friendly field** - Administrators can pick a user’s nationality on the Add User and Edit User screens.
* **Shortcode wrapper** - `[wpdatatable_national]` injects the current viewer’s nationality as `var1` into the native `[wpdatatable]` shortcode.
* **Zero SQL tweaks** - Use wpDataTables’ existing **%VAR1%** placeholder in your queries. No custom filters or hooks required.

---

## Requirements

| Software | Version |
|----------|---------|
| WordPress | 6.2 or later |
| PHP | 7.4 or later |
| wpDataTables | 5.0 or later |

---

## Installation

1. Download this repository as a ZIP file or clone it into the `wp-content/plugins/` directory.
2. In the WordPress dashboard, go to **Plugins → Installed Plugins** and click **Activate** under **wpDataTables Nationality Support**.

> **Tip:** The plugin ships with two nationality options (US and UK). You can add more by editing the `$choices` array in the source code.

---

## Setting a user's nationality

1. Go to **Users → All Users**.
2. Click **Edit** under any account.
3. Scroll to **Additional Profile Information**.
4. Choose **U.S.** or **U.K.** from the **Nationality** dropdown.
5. Click **Update User**.

The selection is stored in `wp_usermeta` with the key **nationality**.

---

## Creating a nationality-aware wpDataTable

1. In **wpDataTables → Create a Table**, choose **MySQL query** (or edit an existing table).
2. Write your query using **%VAR1%** where you would place the two-letter country code.

   ```sql
   SELECT
       posted,
       event_date  AS Event,
       Title,
       Venue,
       Tags,
       Recur       AS Rec,
       Earned      AS Earn,
       Edit,
       lead_days,
       lead_factor
   FROM publish_user_view_%VAR1%
   WHERE Publisher = '%CURRENT_USER_LOGIN%'
   ```
3. Save the table and note its ID (for example, **17**).

---

## Embedding the table in pages or posts

Use the wrapper shortcode instead of the standard `[wpdatatable]`:

```text
[wpdatatable_national id="17"]
```

### Optional fallback

If the viewer is not logged in, or the nationality meta is empty, supply a default:

```text
[wpdatatable_national id="17" fallback="US"]
```

---

## How it works under the hood

1. The shortcode checks the current user's `nationality` meta.
2. It builds the inner shortcode `[wpdatatable id="17" var1="UK"]` (for example).
3. wpDataTables replaces **%VAR1%** with `UK` and runs the query.

---

## Extending the nationality list

Open `wpdatatables-nationality-support.php`, locate the `$choices` array, and add more ISO 3166-1 alpha-2 codes:

```php
$choices = [
    ''   => '- Select -',
    'US' => 'U.S.',
    'UK' => 'U.K.',
    'CA' => 'Canada',
    'AU' => 'Australia',
];
```

---

## Frequently asked questions

| Question | Answer |
|----------|---------|
| **What happens if a user has no nationality set?** | The shortcode passes the `fallback` value, or an empty string if none is provided. |
| **Can I use a different %VARx% slot?** | Yes. Change `var1` in the shortcode and **%VAR1%** in your SQL to match the same slot number. |
| **Is the nationality meta available to other plugins or themes?** | Yes. It is standard user meta, so call `get_user_meta( $user_id, 'nationality', true )`. |

---

## License

This project is licensed under the MIT License. See **LICENSE** for details.
