<?php
// src/lib/provider.php
// Controller that chooses provider (OpenAI first, HuggingFace fallback).
// Exposes function analyze_text_via_providers($text): array

require_once __DIR__ . '/openai.php';
require_once __DIR__ . '/huggingface.php';

// Try to load API keys from environment or optional config
function _get_api_keys() {
    $hf = getenv('HF_API_KEY') ?: null;
    $oa = getenv('OPENAI_API_KEY') ?: null;

    // If config.php exposes values differently, try reading it.
    // Some setups might have $config array — attempt to include it if exists
    if (file_exists(__DIR__ . '/../config/config.php')) {
        // include returns array in some templates; avoid re-declaring $conn
        $cfg = include_once __DIR__ . '/../config/config.php';
        if (is_array($cfg)) {
            if (empty($hf) && isset($cfg['hf_api_key'])) $hf = $cfg['hf_api_key'];
            if (empty($oa) && isset($cfg['openai_api_key'])) $oa = $cfg['openai_api_key'];
        } elseif (isset($GLOBALS['HF_API_KEY']) || isset($GLOBALS['OPENAI_API_KEY'])) {
            // some config variants may set global vars
            if (empty($hf) && !empty($GLOBALS['HF_API_KEY'])) $hf = $GLOBALS['HF_API_KEY'];
            if (empty($oa) && !empty($GLOBALS['OPENAI_API_KEY'])) $oa = $GLOBALS['OPENAI_API_KEY'];
        }
    }

    return ['openai' => $oa, 'huggingface' => $hf];
}

/**
 * Analyze text using OpenAI then HuggingFace fallback.
 * Returns:
 *  ['success'=>true,'data'=>['score'=>int,'tone_tags'=>[], 'summary'=>string, 'provider'=>'openai'|'huggingface'|'local']]
 * or ['success'=>false,'error'=>string]
 */
function analyze_text_via_providers(string $text) : array {
    $keys = _get_api_keys();
    $openaiKey = $keys['openai'];
    $hfKey = $keys['huggingface'];

    // Try OpenAI if key present
    if (!empty($openaiKey)) {
        $oa = openai_analyze_text($text, $openaiKey);
        if (isset($oa['success']) && $oa['success'] === true && isset($oa['data'])) {
            $d = $oa['data'];
            // ensure score fallback
            if (!isset($d['score']) || $d['score'] === null) {
                // try compute heuristic fallback score if missing
                $d['score'] = null;
            }
            return ['success' => true, 'data' => [
                'score' => $d['score'],
                'tone_tags' => $d['tone_tags'] ?? [],
                'summary' => $d['summary'] ?? '',
                'provider' => 'openai',
                'provider_raw' => $oa['raw'] ?? null
            ]];
        } else {
            // record failure reason (but continue to HF)
            $openaiError = $oa['error'] ?? ($oa['raw'] ?? 'unknown');
        }
    } else {
        $openaiError = 'NO_OPENAI_KEY';
    }

    // Try HuggingFace if key present
    if (!empty($hfKey)) {
        $hf = huggingface_analyze_text($text, $hfKey);
        if (isset($hf['success']) && $hf['success'] === true && isset($hf['data'])) {
            $d = $hf['data'];
            return ['success' => true, 'data' => [
                'score' => $d['score'],
                'tone_tags' => $d['tone_tags'] ?? [],
                'summary' => $d['summary'] ?? '',
                'provider' => 'huggingface',
                'provider_raw' => $hf['raw'] ?? null
            ]];
        } else {
            $hfError = $hf['error'] ?? ($hf['raw'] ?? 'unknown');
        }
    } else {
        $hfError = 'NO_HF_KEY';
    }

    // Both failed — return combined error
    $errors = [];
    if (!empty($openaiError)) $errors['openai'] = $openaiError;
    if (!empty($hfError)) $errors['huggingface'] = $hfError;

    return ['success' => false, 'error' => 'ALL_PROVIDERS_FAILED', 'details' => $errors];
}
