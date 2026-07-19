# Kkkonrad Omnibus

Moduł Magento 2 rejestrujący historię obowiązujących cen produktów i wyliczający
najniższą cenę z okresu poprzedzającego obniżkę. Domyślny okres obliczeniowy
wynosi 30 dni.

Moduł działa w kontekście produktu, witryny (`website`) i grupy klientów. Źródłem
cen jest standardowy indeks Magento `catalog_product_index_price`, dzięki czemu
uwzględniane są między innymi ceny specjalne, katalogowe reguły cenowe, zakres
witryny i ceny właściwe dla grup klientów.

> Moduł jest narzędziem technicznym wspierającym prezentację cen. Nie zastępuje
> analizy prawnej dotyczącej konkretnego sklepu, rynku lub rodzaju promocji.

## Najważniejsze funkcje

- historia cen zapisana jako przedziały obowiązywania;
- osobny, zoptymalizowany indeks bieżącego stanu Omnibus;
- automatyczne rejestrowanie zmian po indeksowaniu cen Magento;
- cykliczne uzgadnianie indeksu Magento z historią modułu;
- najniższa cena liczona dla kontekstu produktu, witryny i grupy klientów;
- obsługa stron produktu, kategorii i widgetów produktowych;
- obsługa produktów konfigurowalnych i zmiany wybranego wariantu;
- zgodność z Luma oraz CSP-compatible Alpine.js w motywach Hyva;
- formatowanie ceny zgodnie z walutą i konfiguracją podatku aktywnego sklepu;
- możliwość wyłączenia komunikatu dla produktu lub grup klientów;
- historia cen w panelu administracyjnym;
- REST extension attribute i pole GraphQL `omnibus_price`;
- komendy CLI do diagnostyki, uzgadniania, czyszczenia i przebudowy danych;
- polskie tłumaczenia `pl_PL`.

## Wymagania

- Magento Open Source lub Adobe Commerce 2.4.x;
- PHP 8.2 lub nowszy;
- działający indeks cen katalogowych Magento;
- poprawnie skonfigurowany Magento cron;
- rozszerzenie `Magento_CatalogWidget`, jeżeli wykorzystywane są widgety
  produktowe;
- Hyva jest opcjonalna — moduł obsługuje również standardowy frontend Magento.

Moduł był rozwijany i sprawdzany na Magento `2.4.8-p5` oraz PHP `8.2`.

## Instalacja

Wszystkie polecenia należy wykonywać z katalogu głównego Magento.

### Instalacja w `app/code`

Repozytorium powinno znaleźć się pod ścieżką:

```text
app/code/Kkkonrad/Omnibus
```

Przykład:

```bash
mkdir -p app/code/Kkkonrad
git clone <URL_REPOZYTORIUM> app/code/Kkkonrad/Omnibus

bin/magento module:enable Kkkonrad_Omnibus
bin/magento setup:upgrade
bin/magento indexer:reindex catalog_product_price kkkonrad_omnibus_price
bin/magento cache:clean
```

### Instalacja przez Composer

Jeżeli repozytorium zostało skonfigurowane jako źródło Composer:

```bash
composer require kkkonrad/module-omnibus
bin/magento module:enable Kkkonrad_Omnibus
bin/magento setup:upgrade
bin/magento indexer:reindex catalog_product_price kkkonrad_omnibus_price
bin/magento cache:clean
```

### Wdrożenie produkcyjne

W środowisku produkcyjnym należy dodatkowo wykonać standardowe kroki wdrożenia
Magento:

```bash
bin/magento maintenance:enable
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento setup:static-content:deploy pl_PL en_US
bin/magento indexer:reindex catalog_product_price kkkonrad_omnibus_price
bin/magento cache:clean
bin/magento maintenance:disable
```

Zakresy językowe w `setup:static-content:deploy` należy dopasować do sklepu.

### Kontrola instalacji

```bash
bin/magento module:status Kkkonrad_Omnibus
bin/magento indexer:status kkkonrad_omnibus_price
bin/magento omnibus:diagnose
```

Oczekiwany stan to włączony moduł, indeksator `Ready` oraz brak potencjalnie
brakujących kontekstów w wyniku diagnostyki.

## Pierwsze uruchomienie i początkowa historia

Po instalacji moduł tworzy pierwszy snapshot na podstawie aktualnego indeksu cen
Magento. Nie może odtworzyć cen, których Magento wcześniej nie przechowywało.

Ma to istotne konsekwencje:

- historia rozpoczyna się w momencie aktywacji modułu;
- promocja aktywna już w chwili instalacji może nie mieć ceny referencyjnej
  sprzed obniżki;
- dla takiej promocji komunikat w trybie `Discounted Products` pozostanie ukryty,
  dopóki moduł nie zarejestruje rzeczywistego cyklu ceny regularnej i kolejnej
  obniżki;
- moduł nie tworzy sztucznej historii za poprzednie 30 dni.

Nie należy używać przebudowy historii jako sposobu na uzyskanie danych
historycznych — przebudowa usuwa istniejącą historię i ponownie tworzy wyłącznie
bieżący snapshot.

## Konfiguracja

Konfiguracja znajduje się w:

**Stores → Configuration → Kkkonrad → Cena Omnibus**

| Ustawienie | Domyślnie | Zakres | Znaczenie |
| --- | --- | --- | --- |
| Enable Price History | Yes | website | Włącza rejestrowanie i udostępnianie danych Omnibus. |
| Calculation Period in Days | 30 | website | Okres używany do obliczania najniższej ceny. Minimalna wartość to 30. |
| History Retention in Days | 365 | default | Czas przechowywania zamkniętych wpisów historii. Nie może być krótszy niż okres obliczeniowy. |
| Automatic History Cleanup | Yes | default | Włącza codzienne usuwanie wygasłych, zamkniętych wpisów. |
| Display on Product Pages | Yes | store view | Niezależnie włącza komunikat na stronie produktu. |
| Display on Product Listings and Category Pages | No | store view | Niezależnie włącza komunikat na listach, stronach kategorii i w widgetach produktowych. |
| Display Mode | Discounted Products | store view | Wyświetla dane tylko dla obniżek albo dla wszystkich produktów. |
| Hide When Lowest Price Matches Current Price | Yes | store view | Ukrywa komunikat, jeżeli cena najniższa jest równa obecnej. |
| Frontend Label | tekst Omnibus | store view | Szablon komunikatu widocznego na frontendzie. |
| Display Configurable Child Prices | Yes | store view | Aktualizuje komunikat po wyborze wariantu produktu konfigurowalnego. |
| Hide for Customer Groups | puste | store view | Wyłącza prezentację dla wybranych grup klientów. |
| Display History on Product Edit Page | Yes | default | Dodaje historię do formularza produktu w panelu. |
| Percentage Difference | Discounts Only | store view | Steruje wartością zmiennej `{percentage}`. |

### Tryby wyświetlania

`Discounted Products` korzysta z `reference_price`, czyli najniższej ceny z
okresu bezpośrednio poprzedzającego wykrytą obniżkę. Komunikat pojawia się tylko
dla aktywnej obniżki posiadającej wiarygodną historię referencyjną.

`All Products` korzysta z `lowest_price`, czyli najniższej zarejestrowanej ceny
w ruchomym okresie. Aby pokazywać komunikat również wtedy, gdy najniższa cena
jest równa cenie obecnej, należy ustawić:

- `Display Mode` na `All Products`;
- `Hide When Lowest Price Matches Current Price` na `No`.

W tym trybie zalecana etykieta to:

```text
Najniższa cena z ostatnich {days} dni: {omnibus_price}
```

W trybie wyłącznie dla obniżek zalecana etykieta to:

```text
Najniższa cena z {days} dni przed obniżką: {omnibus_price}
```

### Zmienne etykiety

- `{days}` — długość okresu obliczeniowego;
- `{omnibus_price}` — sformatowana cena referencyjna lub najniższa;
- `{percentage}` — procentowa różnica pomiędzy ceną wskazaną w komunikacie a
  ceną aktualną.

Dozwolone jest podstawowe formatowanie HTML: `span`, `i`, `u` i `b`. Pozostała
treść jest oczyszczana przed wyświetleniem.

### Konfiguracja z CLI

Przykład prezentacji ceny dla wszystkich produktów:

```bash
bin/magento config:set kkkonrad_omnibus/general/display_mode all
bin/magento config:set kkkonrad_omnibus/general/hide_equal 0
bin/magento config:set \
  kkkonrad_omnibus/general/label \
  'Najniższa cena z ostatnich {days} dni: {omnibus_price}'
bin/magento cache:clean config translate layout block_html full_page
```

Dla zakresu witryny lub widoku sklepu należy użyć standardowych parametrów
Magento `--scope` i `--scope-code`.

### Wyłączenie na poziomie produktu

Produkt posiada atrybut `hide_omnibus_price` (`Hide Omnibus Price`) o zakresie
store view. Można go zmienić:

- na formularzu edycji produktu;
- masowo z poziomu listy produktów w panelu administracyjnym.

## Panel administracyjny

Historia jest dostępna w:

**Catalog → Omnibus Price History**

Grid umożliwia filtrowanie między innymi po SKU, witrynie, grupie klientów,
cenach, walucie, czasie obowiązywania i źródle zmiany. Dostępne są także:

- przejście do produktu;
- masowe usuwanie wpisów historii;
- przebudowa całej historii;
- podgląd ostatnich wpisów na formularzu produktu.

Uprawnienia można nadać osobno dla konfiguracji, podglądu, usuwania,
przebudowy historii i zmiany widoczności na produkcie.

## Jak działa rejestrowanie cen

Głównym źródłem jest tabela `catalog_product_index_price`.

Przepływ danych:

1. Magento przebudowuje indeks cen katalogowych.
2. Plugin modułu odczytuje cenę regularną i obowiązującą dla każdego kontekstu.
3. Bieżący przedział historii zostaje zamknięty, jeżeli cena się zmieniła.
4. Moduł zapisuje nowy przedział obowiązywania ceny.
5. W przypadku rozpoczęcia obniżki obliczana i zamrażana jest cena referencyjna.
6. Materializowany indeks jest aktualizowany na potrzeby szybkiego frontendu i
   API.

Obniżka jest aktywna, gdy cena obowiązująca jest niższa od ceny regularnej.
Nowa obniżka zostaje wykryta, gdy produkt przechodzi ze stanu bez obniżki do
stanu obniżki albo jego cena zostaje obniżona ponownie.

Okres jest liczony w strefie czasowej domyślnego store view danej witryny, a
dane w bazie są zapisywane w UTC.

## Indeksator i cron

Identyfikator indeksatora:

```text
kkkonrad_omnibus_price
```

Zalecany tryb produkcyjny:

```bash
bin/magento indexer:set-mode schedule kkkonrad_omnibus_price
bin/magento indexer:reindex catalog_product_price kkkonrad_omnibus_price
```

Moduł definiuje dwa zadania cron:

| Zadanie | Harmonogram | Działanie |
| --- | --- | --- |
| `kkkonrad_omnibus_reconcile` | 17 minut po każdej godzinie | Porównuje bieżący indeks Magento z historią Omnibus. |
| `kkkonrad_omnibus_cleanup` | codziennie o 02:31 | Usuwa zamknięte wpisy starsze od okresu retencji. |

Cron Magento musi być uruchamiany regularnie, na przykład:

```cron
* * * * * php /path/to/magento/bin/magento cron:run 2>&1 | grep -v "Ran jobs by schedule" >> /path/to/magento/var/log/magento.cron.log
```

Ścieżki i sposób uruchamiania PHP należy dostosować do środowiska. W projektach
Docker, Warden lub DDEV trzeba używać właściwego wrappera.

## Komendy CLI

### Diagnostyka

```bash
bin/magento omnibus:diagnose
```

Pokazuje konfigurację okresu i retencji oraz porównuje liczbę kontekstów w
indeksie Magento, indeksie Omnibus i historii.

### Ręczne uzgodnienie

```bash
bin/magento omnibus:reconcile
```

Odczytuje bieżący indeks cen Magento i zapisuje brakujące zmiany do modułu.
Przydatne po imporcie, bezpośredniej integracji lub podejrzeniu pominiętej
zmiany.

### Czyszczenie historii

```bash
bin/magento omnibus:history:cleanup
```

Usuwa wyłącznie zamknięte wpisy starsze od ustawionej retencji. Bieżące otwarte
przedziały nie są usuwane.

### Przebudowa historii

```bash
bin/magento omnibus:rebuild --force
```

> Uwaga: komenda usuwa całą historię i indeks modułu, a następnie tworzy nowy
> snapshot. Operacja jest nieodwracalna bez kopii bazy danych.

Przed wykonaniem w produkcji należy utworzyć backup i zaplanować okno
serwisowe. Operacja jest synchroniczna i na dużych katalogach może trwać długo.

## Baza danych

Moduł tworzy dwie tabele deklaratywne.

### `kkkonrad_omnibus_price_history`

Historia przedziałowa. Jeden wpis zawiera:

- produkt, witrynę i grupę klientów;
- walutę bazową witryny;
- cenę regularną i obowiązującą;
- `valid_from` oraz `valid_to`;
- źródło zmiany.

`valid_to = NULL` oznacza obecnie obowiązujący przedział.

### `kkkonrad_omnibus_price_index`

Materializowany bieżący stan dla każdego kontekstu. Zawiera między innymi:

- cenę aktualną i regularną;
- najniższą cenę w ruchomym okresie (`lowest_price`);
- cenę sprzed aktywnej obniżki (`reference_price`);
- początek promocji i granice okresu referencyjnego;
- flagę aktywnej obniżki.

Kod aplikacyjny powinien korzystać z API modułu, nie wykonywać bezpośrednich
zapisów do tych tabel.

## API PHP

Najważniejsze kontrakty znajdują się w `Api/`:

- `OmnibusPriceProviderInterface` — pobieranie danych dla jednego lub wielu
  produktów;
- `LowestPriceCalculatorInterface` — obliczanie minimum dla przedziału;
- `PromotionDetectorInterface` — wykrywanie aktywnej i nowej obniżki;
- `PeriodResolverInterface` — wyznaczanie początku okresu;
- `Data/OmnibusPriceInterface` — obiekt wynikowy.

Do odczytu wielu produktów należy używać `getList()`, aby uniknąć zapytań N+1.

## REST API

Standardowe odpowiedzi repozytorium produktu zawierają:

```text
extension_attributes.omnibus_price
```

Przykład:

```bash
curl -H 'Authorization: Bearer <TOKEN>' \
  https://example.com/rest/V1/products/24-MB01
```

Obiekt ceny zawiera pola:

- `current_price`;
- `reference_price`;
- `lowest_price`;
- `currency_code`;
- `period_days`;
- `promotion_started_at`;
- `has_active_discount`;
- `message`.

Dostęp do endpointu podlega standardowej autoryzacji Magento.

## GraphQL

Pole `omnibus_price` jest dodane do `ProductInterface` i obsługiwane przez
resolver batchowy.

```graphql
{
  products(filter: {sku: {eq: "24-MB01"}}) {
    items {
      sku
      omnibus_price {
        current_price
        reference_price
        lowest_price
        currency_code
        period_days
        promotion_started_at
        has_active_discount
        message
      }
    }
  }
}
```

Wartość `reference_price` może być `null`, jeżeli nie rozpoczęła się obniżka lub
moduł nie posiada historii sprzed jej rozpoczęcia.

## Frontend i Hyva

Komunikat jest dołączany do standardowego renderera ceny produktu. Kolekcje na
listach i w widgetach są przygotowywane zbiorczo, aby ograniczać liczbę zapytań.

Dla produktów konfigurowalnych moduł udostępnia komunikaty wariantów i reaguje
na zmianę wyboru. Integracja Hyva:

- rejestruje nazwany komponent `Alpine.data`;
- nie używa `unsafe-inline`;
- rejestruje skrypt przez Hyva CSP;
- działa bez zależności od RequireJS.

Jeżeli projekt nadpisuje renderer ceny lub całkowicie zastępuje strukturę
HTML ceny, może być konieczne dostosowanie pluginu lub szablonu modułu.

## Tłumaczenia

Polski słownik znajduje się w `i18n/pl_PL.csv`.

Aby zobaczyć polskie teksty:

- ustaw locale store view na `Polski (Polska)`;
- dla panelu ustaw `Interface Locale` administratora na `Polski (Polska)`;
- wyczyść cache `translate` po zmianach w słowniku.

```bash
bin/magento cache:clean translate config
```

Po dodaniu nowej frazy należy użyć `__()` w PHP albo `translate="true"` /
`translate="label comment"` w odpowiednim XML i dopisać tłumaczenie do CSV.

## Struktura katalogów

```text
Api/                         publiczne kontrakty PHP
Block/                       bloki frontendu i panelu
Console/Command/             komendy bin/magento
Controller/Adminhtml/        akcje panelu administracyjnego
Cron/                        zadania cykliczne
Indexer/                     indeksator Omnibus
Model/                       logika domenowa i konfiguracja
Model/ResourceModel/         zapis historii i kolekcje
Plugin/                      integracje z cenami i repozytorium produktu
Setup/Patch/Data/            atrybut produktu
Test/Unit/                   testy jednostkowe
Ui/                          grid i modyfikator formularza produktu
etc/                         DI, schema, cron, ACL, GraphQL i konfiguracja
i18n/                        słowniki tłumaczeń
view/adminhtml/              widoki panelu
view/frontend/               szablony Luma i Hyva
```

## Rozpoczęcie developmentu

Po zmianach w PHP, XML lub DI zalecany zestaw kontroli:

```bash
find app/code/Kkkonrad/Omnibus -name '*.php' -print0 | xargs -0 -n1 php -l

vendor/bin/phpunit \
  --no-extensions \
  -c dev/tests/unit/phpunit.xml.dist \
  app/code/Kkkonrad/Omnibus/Test/Unit

vendor/bin/phpcs \
  --standard=Magento2 \
  --extensions=php,phtml \
  app/code/Kkkonrad/Omnibus

bin/magento setup:di:compile
bin/magento cache:clean
```

Po zmianie algorytmu cen należy dodatkowo sprawdzić:

1. produkt bez obniżki;
2. rozpoczęcie pierwszej obniżki;
3. kolejną obniżkę podczas trwającej promocji;
4. powrót do ceny regularnej;
5. różne witryny i grupy klientów;
6. produkt konfigurowalny przed i po wyborze wariantu;
7. stronę kategorii oraz widget produktowy;
8. REST i GraphQL;
9. wartości z podatkiem i bez podatku;
10. zmianę dnia w skonfigurowanej strefie czasowej.

Do testów funkcjonalnych należy zmieniać ceny przez standardowe mechanizmy
Magento, a następnie przebudowywać indeks `catalog_product_price`. Bezpośrednia
edycja tabel modułu nie odzwierciedla rzeczywistego przepływu produkcyjnego.

## Diagnostyka problemów

### Komunikat nie pojawia się na produkcie

Sprawdź kolejno:

```bash
bin/magento module:status Kkkonrad_Omnibus
bin/magento config:show kkkonrad_omnibus/general/display_place
bin/magento config:show kkkonrad_omnibus/general/display_mode
bin/magento config:show kkkonrad_omnibus/general/hide_equal
bin/magento indexer:status catalog_product_price kkkonrad_omnibus_price
bin/magento omnibus:diagnose
```

Następnie zweryfikuj:

- czy moduł jest włączony dla witryny;
- czy włączono wyświetlanie dla danego miejsca: strony produktu albo listy/kategorii;
- czy produkt nie ma włączonego `hide_omnibus_price`;
- czy grupa klienta nie jest wykluczona;
- czy aktywny tryb wymaga obniżki;
- czy istnieje historia sprzed obniżki;
- czy cache strony i bloków został wyczyszczony.

### Indeks ma brakujące konteksty

```bash
bin/magento indexer:reindex catalog_product_price
bin/magento omnibus:reconcile
bin/magento indexer:reindex kkkonrad_omnibus_price
bin/magento omnibus:diagnose
```

Jeżeli problem wraca, należy sprawdzić działanie cron, logi indeksatorów i sposób
importowania cen.

### Przydatne logi

```text
var/log/system.log
var/log/exception.log
var/log/cron.log
var/log/debug.log
```

W środowisku produkcyjnym dostępność konkretnych plików zależy od konfiguracji
logowania Magento.

## Ograniczenia

- Moduł nie rekonstruuje historii sprzed instalacji.
- Reguły koszyka i kupony nie są częścią katalogowego indeksu cen, więc nie są
  uwzględniane.
- Dla bundle i innych dynamicznie konfigurowanych produktów zapisywana jest cena
  indeksowana przez Magento, a nie każda możliwa kombinacja opcji.
- Bezpośrednie zmiany w bazie są widoczne dopiero po reindeksacji i uzgodnieniu.
- Przebudowa historii działa synchronicznie.
- Historia jest przechowywana w walucie bazowej witryny; frontend konwertuje ją
  do waluty aktywnego sklepu przy użyciu bieżących kursów Magento.

## Aktualizacja modułu

Przed aktualizacją należy wykonać backup bazy danych. Po pobraniu nowej wersji:

```bash
bin/magento maintenance:enable
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento indexer:reindex catalog_product_price kkkonrad_omnibus_price
bin/magento cache:clean
bin/magento maintenance:disable
```

## Wyłączenie modułu

```bash
bin/magento module:disable Kkkonrad_Omnibus
bin/magento cache:clean
```

Samo wyłączenie modułu nie powinno być traktowane jako usunięcie historii.
Przed ręcznym usuwaniem tabel lub pakietu należy wykonać backup oraz ustalić
wymagany okres przechowywania danych cenowych.

## Licencja

OSL-3.0 — zgodnie z polem `license` w `composer.json`.
