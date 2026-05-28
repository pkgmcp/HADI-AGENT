<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class DuskTest extends DuskTestCase
{
    public function test_dashboard_loads(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->assertSee('HADI')
                ->assertSee('Dashboard');
        });
    }

    public function test_agents_page_loads(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/agents')
                ->assertSee('AI Agents');
        });
    }

    public function test_mcp_page_loads(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/mcp')
                ->assertSee('MCP Server');
        });
    }
}
