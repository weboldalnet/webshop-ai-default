<?php

namespace Weboldalnet\WebshopAiDefault\Services\Webshop;

use Illuminate\Support\Facades\Log;

/**
 * A pénztárhoz beállított kérdőív (ContactQa) kezelése.
 *
 * A kérdőív a PROJEKT modellje, nem a csomagé – a csomag sehol máshol sem
 * hivatkozik App\ osztályokra, ezért itt sem drótozzuk be: az osztálynevet a
 * config adja, és ha az osztály nincs meg (a csomagot kérdőív nélküli
 * projektbe teszik), a funkció egyszerűen kikapcsolva marad.
 */
class WebshopCheckoutQaService
{
    /** A pénztárhoz választott kérdőív azonosítója ebben a beállításban van */
    const SETTING_KEY = 'site_checkout_qa_id';

    /**
     * A kérdőív-modell osztályneve, vagy null, ha a projektben nincs ilyen.
     */
    public static function modelClass(): ?string
    {
        $class = config('webshop.contact_qa_model', '\App\Models\ContactQa');

        return ($class && class_exists($class)) ? $class : null;
    }

    /**
     * Van-e egyáltalán kérdőív-támogatás ebben a projektben?
     */
    public static function isSupported(): bool
    {
        return self::modelClass() !== null;
    }

    /**
     * A választható kérdőívek [id => név] formában, az admin legördülőhöz.
     */
    public static function getSelectableQas(): array
    {
        $class = self::modelClass();
        if (!$class) {
            return [];
        }

        try {
            return $class::orderBy('name')->pluck('name', 'id')->toArray();
        } catch (\Throwable $e) {
            Log::warning('Webshop: a kérdőívek listája nem tölthető be.', ['error' => $e->getMessage()]);

            return [];
        }
    }

    /**
     * A pénztárhoz beállított kérdőív, vagy null.
     */
    public static function getCheckoutQa()
    {
        $class = self::modelClass();
        $id = WebshopSettingsService::get(self::SETTING_KEY);

        if (!$class || !$id) {
            return null;
        }

        try {
            return $class::find($id);
        } catch (\Throwable $e) {
            Log::warning('Webshop: a pénztár kérdőíve nem tölthető be.', [
                'qa_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * A beküldött válaszok normalizálása a rendeléshez mentendő alakra.
     *
     * A site-oldali kérdőív-nézetek `answers[<kérdés id>][question]` és
     * `answers[<kérdés id>][answers][]` néven küldik az adatot – ez a kapcsolat
     * oldal évek óta használt szerkezete, ezért erre építünk.
     *
     * A kérdés SZÖVEGÉT is eltesszük, nem csak az azonosítóját: a kérdőív
     * később átszerkeszthető vagy törölhető, a rendeléshez viszont annak kell
     * tartoznia, amit a vásárló ténylegesen látott.
     *
     * @return array|null null, ha nincs mit menteni
     */
    public static function buildOrderQaData(array $requestData): ?array
    {
        $qa = self::getCheckoutQa();
        if (!$qa) {
            return null;
        }

        $answers = $requestData['answers'] ?? null;
        if (!is_array($answers) || empty($answers)) {
            return null;
        }

        $items = [];
        foreach ($answers as $questionId => $row) {
            if (!is_array($row)) {
                continue;
            }

            $question = trim((string) ($row['question'] ?? ''));
            if ($question === '') {
                continue;
            }

            // A válasz lehet több értékű is (checkbox), ezért mindig tömbként kezeljük
            $given = $row['answers'] ?? [];
            if (!is_array($given)) {
                $given = [$given];
            }

            $given = array_values(array_filter(array_map(function ($value) {
                return is_scalar($value) ? trim((string) $value) : null;
            }, $given), function ($value) {
                return $value !== null && $value !== '';
            }));

            $items[] = [
                'id' => $questionId,
                'question' => $question,
                'answers' => $given,
            ];
        }

        if (empty($items)) {
            return null;
        }

        return [
            'qa_id' => $qa->id,
            'qa_name' => $qa->name,
            'items' => $items,
        ];
    }
}
