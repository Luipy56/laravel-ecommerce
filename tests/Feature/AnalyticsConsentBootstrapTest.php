<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class AnalyticsConsentBootstrapTest extends TestCase
{
    public function test_welcome_bootstraps_consent_mode_defaults(): void
    {
        config([
            'analytics.enabled' => true,
            'analytics.gtm_container_id' => 'GTM-TEST123',
            'analytics.ga4_measurement_id' => 'G-TESTMEAS',
        ]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('window.__SERRA_ANALYTICS__', false);
        $response->assertSee('GTM-TEST123', false);
        $response->assertSee('G-TESTMEAS', false);
        $response->assertSee("analytics_storage: 'denied'", false);
        // GTM script must not be hard-coded in HTML (loads after consent in JS)
        $response->assertDontSee('googletagmanager.com/gtm.js', false);
    }

    public function test_welcome_exposes_disabled_analytics_when_flag_off(): void
    {
        config([
            'analytics.enabled' => false,
            'analytics.gtm_container_id' => 'GTM-TEST123',
            'analytics.ga4_measurement_id' => 'G-TESTMEAS',
        ]);

        $response = $this->get('/');
        $response->assertOk();
        $response->assertSee('"enabled":false', false);
    }
}
