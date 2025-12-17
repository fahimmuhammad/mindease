<?php
/**
 * MindEase Configuration File
 * ---------------------------
 * This file stores ONLY sensitive credentials and never lives inside /public.
 * All backend scripts load this using:
 *
 *     $config = include __DIR__ . '/config.php';
 *
 * Change values as needed.
 */

return [

    // ---------------------------------------------
    // DATABASE CONFIG
    // ---------------------------------------------
    "db" => [
        "host" => "localhost",
        "user" => "root",
        "pass" => "",
        "name" => "mindease",
    ],

    // ---------------------------------------------
    // AI PROVIDER KEYS
    // ---------------------------------------------
    "openai_api_key" => "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",   // <-- paste new rotated key
    "huggingface_api_key" => "",                  // optional, leave empty if not using

    // ---------------------------------------------
    // PROVIDER ORDER
    // ---------------------------------------------
    "provider_priority" => "openai",              // first choice
    "provider_fallback" => "huggingface",         // fallback provider

    // ---------------------------------------------
    // MODEL SETTINGS
    // ---------------------------------------------
    "openai_model" => "gpt-3.5-turbo-0125",
    "huggingface_model" => "distilbert-base-uncased", // not used unless key added

    // ---------------------------------------------
    // SECURITY
    // ---------------------------------------------
    "allowed_origins" => ["localhost", "127.0.0.1"],

];
