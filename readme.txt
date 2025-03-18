=== JŠM WP Event Calendar With WooCommerce ===
Contributors: jansmidl
Tags: calendar, events, event calendar, scheduling, responsive calendar, woocommerce
Requires at least: 5.0
Tested up to: 6.4
Requires PHP: 7.2
Stable tag: 1.0.0
License: Proprietary

Plugin pro zobrazení kalendáře událostí s responzivním designem s napojením na produkty woocommerce.

== Description ==

JŠM WP Event Calendar With WooCommerce je jednoduchý a efektivní plugin pro správu a zobrazení událostí ve formě kalendáře nebo seznamu s možností propojení s produkty WooCommerce. Díky plně přizpůsobitelnému vzhledu prostřednictvím administrace je nyní ještě flexibilnější.

= Hlavní funkce =

* **Responzivní design** - Kalendář se přizpůsobí velikosti obrazovky, včetně mobilních zařízení.
* **Vícejazyčná podpora** - Plná podpora češtiny a angličtiny.
* **Flexibilní zobrazení** - Možnost zobrazit události jako kalendář nebo seznam.
* **Vlastní události** - Snadné přidávání a správa událostí s detaily jako datum, čas, popis a URL.
* **Propojení s WooCommerce** - Možnost propojit události s produkty ve vašem e-shopu.
* **Shortcody** - Jednoduché vložení kalendáře nebo seznamu událostí kamkoliv na váš web.
* **Přizpůsobitelný vzhled** - Úplná kontrola nad barvami, stíny, zaoblením rohů a dalšími vizuálními aspekty.
* **Intuitivní správa nastavení** - Přehledné rozhraní s color pickery a živým náhledem.

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

= Přizpůsobení vzhledu =

Plugin nabízí kompletní kontrolu nad vzhledem kalendáře přímo z administrace WordPress:

* Nastavení všech barev pomocí intuitivních color pickerů
* Možnost upravit stíny pro různé prvky
* Nastavení zaoblení rohů pro tlačítka a další prvky
* Živý náhled změn
* Možnost obnovit výchozí nastavení jedním kliknutím

== Installation ==

1. Stáhněte soubory pluginu a nahrajte je do složky `/wp-content/plugins/jsm-wp-event-calendar`
2. Aktivujte plugin v sekci 'Pluginy' v administraci WordPressu
3. Pro přidání nové události přejděte do sekce 'Události' v menu administrace
4. Pro nastavení vzhledu přejděte do 'Události' > 'Nastavení'
5. Pro zobrazení kalendáře nebo seznamu událostí použijte shortcody `[event_calendar]` nebo `[event_list]`

== Frequently Asked Questions ==

= Jak přidat novou událost? =

Přejděte do sekce 'Události' v menu administrace a klikněte na 'Přidat novou'.

= Jak změnit barvy kalendáře? =

Přejděte do 'Události' > 'Nastavení' v administračním menu a použijte color pickery pro nastavení všech barev. Změny se projeví okamžitě po uložení.

= Mohu obnovit výchozí nastavení vzhledu? =

Ano, na stránce 'Události' > 'Nastavení' najdete tlačítko 'Obnovit výchozí nastavení', které vrátí všechny hodnoty na původní.

= Mohu zobrazit pouze nadcházející události? =

Ano, pro seznam událostí použijte shortcode `[event_list past="no"]`. Pro kalendář se ve výchozím nastavení zobrazují všechny události v daném měsíci.

= Jak propojím událost s produktem WooCommerce? =

Při vytváření nebo úpravě události můžete v metaboxu nastavit URL adresu, která může směřovat na produkt ve vašem e-shopu.

= Je plugin kompatibilní s Gutenbergem? =

Ano, shortcody můžete vložit do bloku Vlastní HTML v editoru Gutenberg.

= Je plugin responzivní pro mobilní zařízení? =

Ano, plugin používá plně responzivní design, který se optimálně zobrazí na zařízeních všech velikostí. Na mobilních zařízeních se kalendář automaticky přepne do přehlednějšího zobrazení.

== Screenshots ==

1. Kalendář událostí
2. Seznam událostí
3. Detail události
4. Mobilní zobrazení
5. Administrace událostí
6. Nastavení vzhledu kalendáře

== Changelog ==

= 1.0.0 =
* První verze pluginu
* Přidána podpora responzivního designu
* Implementovány základní shortcody [event_calendar] a [event_list]
* Přidána možnost přizpůsobení vzhledu přímo v administraci
* Integrace s WooCommerce

== Upgrade Notice ==

= 1.0.0 =
První verze pluginu s kompletními funkcemi pro správu a zobrazení kalendáře událostí

== About the Author ==

Plugin JŠM WP Event Calendar With WooCommerce byl vytvořen [Janem Šmídlem](https://jansmidl.cz).