<?php

declare(strict_types=1);

/**
 * Status-Kachel der UnifiProtect-Instanzen (HTML-SDK).
 *
 * Alle Instanzen teilen sich libs/tile.html. Das Modul haelt den Zustand in einem Buffer,
 * liefert ihn mit der initialen Kachel aus und pusht jede Aenderung an offene Visualisierungen.
 * Der Zustand geht an den Browser - es duerfen nur Felder aufgenommen werden, die angezeigt
 * werden sollen (niemals eine API-Antwort unveraendert durchreichen: sie kann Schluessel
 * oder andere sensible Daten enthalten).
 */
trait UnifiProtectTile
{
    public function GetVisualizationTile(): string
    {
        // Buffer sind nach einem Neustart von Symcon leer. Damit die Kachel nicht bis zum
        // naechsten Update leer bleibt, werden die Daten hier einmalig angefordert.
        if ($this->GetBuffer('TileState') === '' && $this->GetStatus() === IS_ACTIVE) {
            $this->TileRefresh();
        }
        $state = $this->TileReadState();
        $state['kind'] = $this->TileKind();
        $state['inactive'] = $this->GetStatus() !== IS_ACTIVE;
        $json = json_encode($state, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
        return str_replace('/*INITIAL_STATE*/null', (string) $json, (string) file_get_contents(__DIR__ . '/tile.html'));
    }

    /** Mischt Werte in den Zustand und sendet den kompletten Zustand an die Visualisierung. */
    private function TileUpdate(array $values): void
    {
        $state = array_replace($this->TileReadState(), $values);
        $state['kind'] = $this->TileKind();
        $state['inactive'] = false;
        $json = (string) json_encode($state, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
        $this->SetBuffer('TileState', $json);
        $this->UpdateVisualizationValue($json);
    }

    private function TileReadState(): array
    {
        $state = json_decode($this->GetBuffer('TileState'), true);
        return is_array($state) ? $state : [];
    }
}
