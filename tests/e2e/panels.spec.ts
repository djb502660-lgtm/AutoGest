import { expect, type Page, test } from '@playwright/test';
import { mkdirSync, writeFileSync } from 'node:fs';
import { join } from 'node:path';

const artifactsDir = join(process.cwd(), 'tests', 'e2e', 'artifacts');

const panels = [
    { name: 'admin', email: 'admin@autogest.test', url: '/dashboard', heading: 'Sistema administrativo' },
    { name: 'asesor', email: 'asesor1@autogest.test', url: '/asesor', heading: 'Dashboard principal' },
    { name: 'mecanico', email: 'mecanico1@autogest.test', url: '/mecanico', heading: 'Panel de Trabajo del Mecánico' },
    { name: 'cliente', email: 'cliente1@autogest.test', url: '/cliente', heading: 'Dashboard principal' },
] as const;

async function shot(page: Page, name: string): Promise<void> {
    mkdirSync(artifactsDir, { recursive: true });
    const buffer = await page.screenshot({ fullPage: true });
    writeFileSync(join(artifactsDir, `${name}.png`), buffer);
}

async function login(page: Page, email: string): Promise<void> {
    await page.goto('/login');
    await page.locator('#email').fill(email);
    await page.locator('#password').fill('password');
    await page.getByRole('button', { name: 'Entrar al sistema' }).click();
}

test.describe('Regresión visual de paneles', () => {
    test('login muestra auth-shell y escala de texto', async ({ page }) => {
        await page.goto('/login');

        await expect(page.locator('.auth-shell')).toBeVisible();
        await expect(page.getByRole('heading', { name: 'Inicia sesión' })).toBeVisible();
        await expect(page.locator('#email')).toBeVisible();
        await expect(page.locator('#password')).toBeVisible();
        await expect(page.locator('.shell')).toHaveCount(0);

        const fontSize = await page.locator('body').evaluate((el) => getComputedStyle(el).fontSize);
        expect(fontSize).toBe('16px');

        await shot(page, '01-login');
    });

    for (const panel of panels) {
        test(`${panel.name} usa shell compartido y sidebar 300px`, async ({ page }) => {
            await login(page, panel.email);
            await page.waitForURL(`**${panel.url}*`);
            await expect(page).toHaveURL(new RegExp(`${panel.url.replace('/', '\\/')}(/)?$`));

            await expect(page.locator('.shell')).toBeVisible();
            await expect(page.locator('.desktop-sidebar')).toBeVisible();
            await expect(page.locator('.mobile-topbar')).toBeHidden();
            await expect(page.getByRole('heading', { name: panel.heading })).toBeVisible();

            const sidebar = await page.locator('.desktop-sidebar').boundingBox();
            expect(sidebar).not.toBeNull();
            expect(Math.round(sidebar?.width ?? 0)).toBe(300);

            const sidebarY = sidebar?.y ?? 0;
            await page.locator('.main').evaluate((el) => {
                el.scrollTop = Math.min(400, el.scrollHeight);
            });
            const sidebarAfter = await page.locator('.desktop-sidebar').boundingBox();
            expect(Math.round(sidebarAfter?.y ?? -1)).toBe(Math.round(sidebarY));

            const pageOverflowX = await page.evaluate(() => document.documentElement.scrollWidth - document.documentElement.clientWidth);
            expect(pageOverflowX).toBeLessThanOrEqual(1);

            const fontSize = await page.locator('body').evaluate((el) => getComputedStyle(el).fontSize);
            expect(fontSize).toBe('16px');

            if (panel.name === 'cliente') {
                await expect(page.locator('.chatbot-fab')).toBeVisible();
                await page.locator('.chatbot-fab').click();
                await expect(page.locator('.chatbot-panel')).toBeVisible();
                await expect(page.getByText('AutoGest Bot')).toBeVisible();
                await expect(page.locator('#chatInput')).toBeVisible();
            }

            await shot(page, `02-${panel.name}`);
        });
    }
});
