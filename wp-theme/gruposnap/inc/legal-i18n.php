<?php
/**
 * GrupoSnap — páginas legales ES / EN (cuerpo desde seed-html/legal).
 *
 * @package Gruposnap
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * @return string[]
 */
function gruposnap_legal_slugs(): array
{
    return array('aviso-legal', 'privacidad', 'cookies', 'accesibilidad');
}

/**
 * Título visible por slug e idioma.
 */
function gruposnap_legal_title(string $slug): string
{
    $map = array(
        'aviso-legal' => array(
            'es' => 'Aviso legal y condiciones de uso',
            'en' => 'Legal notice and terms of use',
        ),
        'privacidad' => array(
            'es' => 'Política de privacidad',
            'en' => 'Privacy policy',
        ),
        'cookies' => array(
            'es' => 'Política de cookies',
            'en' => 'Cookie policy',
        ),
        'accesibilidad' => array(
            'es' => 'Declaración de accesibilidad',
            'en' => 'Accessibility statement',
        ),
    );
    $lang = function_exists('gruposnap_current_lang') ? gruposnap_current_lang() : 'es';
    if (isset($map[$slug][$lang])) {
        return $map[$slug][$lang];
    }

    return $slug;
}

/**
 * Lead (hero) por slug e idioma.
 */
function gruposnap_legal_lead(string $slug): string
{
    $map = array(
        'privacidad' => array(
            'es' => 'Qué datos personales tratamos, con qué finalidad, durante cuánto tiempo y qué derechos puedes ejercer.',
            'en' => 'What personal data we process, for what purpose, how long we keep it, and what rights you can exercise.',
        ),
        'cookies' => array(
            'es' => 'Qué cookies y tecnologías similares usamos y cómo puedes gestionarlas.',
            'en' => 'What cookies and similar technologies we use and how you can manage them.',
        ),
        'aviso-legal' => array(
            'es' => 'Información del titular del sitio, condiciones de uso y marco legal aplicable.',
            'en' => 'Site owner information, terms of use, and applicable legal framework.',
        ),
        'accesibilidad' => array(
            'es' => 'Compromiso de accesibilidad, medidas aplicadas y cómo reportar barreras.',
            'en' => 'Accessibility commitment, measures applied, and how to report barriers.',
        ),
    );
    $lang = function_exists('gruposnap_current_lang') ? gruposnap_current_lang() : 'es';

    return $map[$slug][$lang] ?? '';
}
