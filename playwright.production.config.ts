import { defineConfig, devices } from '@playwright/test';

const productionUrl = process.env.PRODUCTION_URL ?? 'https://autogest-jlm7.onrender.com';

export default defineConfig({
    testDir: './tests/e2e',
    testMatch: 'mobile-login-production.spec.ts',
    timeout: 90_000,
    fullyParallel: false,
    workers: 1,
    retries: 1,
    reporter: [['list']],
    use: {
        baseURL: productionUrl,
        locale: 'es-EC',
        screenshot: 'only-on-failure',
        trace: 'retain-on-failure',
    },
    projects: [
        {
            name: 'mobile-chrome',
            use: { ...devices['Pixel 5'] },
        },
    ],
});
