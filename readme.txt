=== JSM WordPress Event Calendar ===
Contributors: yourname
Tags: calendar, events, event calendar, scheduling
Requires at least: 5.0
Tested up to: 6.3
Requires PHP: 7.2
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Jednoduchý responzivní kalendář událostí s podporou češtiny a angličtiny pro WordPress.

== Description ==

JSM WordPress Event Calendar je jednoduchý a efektivní plugin pro správu a zobrazení událostí ve formě kalendáře nebo seznamu.

= Hlavní funkce =

* **Responzivní design** - Kalendář se přizpůsobí velikosti obrazovky, včetně mobilních zařízení.
* **Vícejazyčná podpora** - Plná podpora češtiny a angličtiny.
* **Flexibilní zobrazení** - Možnost zobrazit události jako kalendář nebo seznam.
* **Vlastní události** - Snadné přidávání a správa událostí s detaily jako datum, čas, popis a URL.
* **Shortcody** - Jednoduché vložení kalendáře nebo seznamu událostí kamkoliv na váš web.

= Použití =

Kalendář událostí můžete do stránky nebo příspěvku vložit pomocí shortcodu:

`[event_calendar]`

Nebo seznam událostí:

`[event_list]`

= Volitelné parametry =

**Pro kalendář:**
* `month` - Číslo měsíce (1-12)
* `year` - Rok (např. 2023)
* `show_list` - Zobrazit seznam událostí pod kalendářem (yes/no)
* `category` - ID nebo slug kategorie pro filtrování událostí

**Pro seznam událostí:**
* `limit` - Počet zobrazených událostí
* `category` - ID nebo slug kategorie pro filtrování událostí
* `past` - Zobrazit proběhlé události (yes/no)
* `layout` - Způsob zobrazení (list/grid)

== Installation ==

1. Stáhněte soubory pluginu a nahrajte je do složky `/wp-content/plugins/jsm-wp-event-calendar`
2. Aktivujte plugin v sekci 'Pluginy' v administraci WordPressu
3. Pro přidání nové události přejděte do sekce 'Události' v menu administrace
4. Pro zobrazení kalendáře nebo seznamu událostí použijte shortcody `[event_calendar]` nebo `[event_list]`

== Frequently Asked Questions ==

= Jak přidat novou událost? =

Přejděte do sekce 'Události' v menu administrace a klikněte na 'Přidat novou'.

= Jak změnit barvy kalendáře? =

Plugin používá základní barvy, které lze upravit pomocí vlastního CSS ve vašem motivu.

= Mohu zobrazit pouze nadcházející události? =

Ano, pro seznam událostí použijte shortcode `[event_list past="no"]`. Pro kalendář se ve výchozím nastavení zobrazují všechny události v daném měsíci.

= Je plugin kompatibilní s Gutenbergem? =

Ano, shortcody můžete vložit do bloku Vlastní HTML v editoru Gutenberg.

== Screenshots ==

1. Kalendář událostí
2. Seznam událostí
3. Detail události
4. Mobilní zobrazení
5. Administrace událostí

== Changelog ==

= 1.0.0 =
* První verze pluginu

== Upgrade Notice ==

= 1.0.0 =
První verze pluginu