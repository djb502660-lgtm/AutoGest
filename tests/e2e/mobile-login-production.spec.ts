import { expect, test } from '@playwright/test';
import { mkdirSync, writeFileSync } from 'node:fs';
import { join } from 'node:path';

const productionUrl = process.env.PRODUCTION_URL ?? 'https://autogest-jlm7.onrender.com';
const artifactsDir = join(process.cwd(), 'tests', 'e2e', 'artifacts', 'mobile-419');

async function shot(page: import('@playwright/test').Page, name: string): Promise<void> {
    mkdirSync(artifactsDir, { recursive: true });
    const buffer = await page.screenshot({ fullPage: true });
    writeFileSync(join(artifactsDir, `${name}.png`), buffer);
}

test('login normal con cookies guarda sesión', async ({ page, context }) => {
        await page.goto('/login');
        await expect(page.getByRole('heading', { name: 'Inicia sesión' })).toBeVisible();
        await shot(page, '01-login-loaded');

        const cookiesBefore = await context.cookies();
        expect(cookiesBefore.some((c) => c.name.includes('session'))).toBeTruthy();

        await page.locator('#email').fill('cliente1@autogest.test');
        await page.locator('#password').fill('password');
        await page.getByRole('button', { name: 'Entrar al sistema' }).click();

        await page.waitForURL('**/cliente**', { timeout: 30_000 });
        await expect(page).not.toHaveURL(/419|login/i);
    await shot(page, '02-login-success');
});

test('formulario cacheado (bfcache) recarga y permite login', async ({ page }) => {
    await page.goto('/login');
    await page.locator('#email').fill('cliente1@autogest.test');
    await page.locator('#password').fill('password');

    await page.goto('/');

    const reloadPromise = page.waitForLoadState('load');
    await page.goBack();
    await reloadPromise;

    await page.locator('#password').fill('password');
    await page.getByRole('button', { name: 'Entrar al sistema' }).click();
    await page.waitForURL('**/cliente**', { timeout: 30_000 });
    await expect(page.locator('body')).not.toContainText('419');
    await expect(page.locator('body')).not.toContainText('Page Expired');
    await shot(page, '03-bfcache-login-success');
});

test('POST sin cookie de sesión devuelve 419 en producción', async ({ playwright }) => {
    const reader = await playwright.request.newContext({ baseURL: productionUrl });
    const poster = await playwright.request.newContext({ baseURL: productionUrl });

    const loginPage = await reader.get('/login');
    const html = await loginPage.text();
    const tokenMatch = html.match(/name="_token" value="([^"]+)"/);
    expect(tokenMatch).not.toBeNull();

    const postWithoutSession = await poster.post('/login', {
        form: {
            _token: tokenMatch![1],
            email: 'cliente1@autogest.test',
            password: 'password',
        },
    });

    expect(postWithoutSession.status()).toBe(419);
    await reader.dispose();
    await poster.dispose();
});
