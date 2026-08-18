import { defineConfig, devices } from '@playwright/test';

const baseURL = process.env.APP_URL ?? 'http://localhost:8000';

export default defineConfig({
    testDir: './tests/e2e',
    timeout: 60_000,
    fullyParallel: false,
    workers: 1,
    retries: 0,
    reporter: [['list']],
    use: {
        baseURL,
        locale: 'es-EC',
        viewport: { width: 1440, height: 900 },
        screenshot: 'off',
        trace: 'off',
    },
    projects: [
        {
            name: 'chromium',
            use: { ...devices['Desktop Chrome'], viewport: { width: 1440, height: 900 } },
        },
    ],
    webServer: {
        command: 'php artisan serve --host=localhost --port=8000',
        url: `${baseURL}/login`,
        reuseExistingServer: true,
        timeout: 30_000,
    },
});
