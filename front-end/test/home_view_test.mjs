import { describe, it, before, after } from 'mocha';
import assert from 'assert';
import pkg from 'selenium-webdriver';
const { Builder, By, until } = pkg;
import { Actions } from 'selenium-webdriver/lib/input.js';

describe('Selenium Tests for Home Page', function () {
    let driver;

    before(async function () {
        this.timeout(10000); //give it 10s to load
        driver = await new Builder().forBrowser('MicrosoftEdge').usingServer().build();
    });

    it('Page Load Test', async function () {
        await driver.get('http://localhost:5173/');
        const pageTitle = await driver.getTitle();

        assert.equal(pageTitle, 'Rulemaking Influence Explorer');
    });

    it('Display Welcome Banner Present', async function () {
      await driver.get('http://localhost:5173/');
  
      const welcomeBanner = await driver.wait(until.elementLocated(By.css('.welcome-banner')), 5000);
      const welcomeText = await welcomeBanner.getText();
      assert.strictEqual(welcomeText, 'Welcome to the Rulemaking Influence Explorer!');
    });

    it('Sort Dropdown Test', async function () {
        this.timeout(10000000);
        try {
            console.log('Navigating to the page...');
            await driver.get('http://localhost:5173/');
    
            console.log('Waiting for sort dropdown to be located...');
            const sortDropdown = await driver.wait(until.elementLocated(By.id('sort-drop-down')), 15000);
            console.log('Sort dropdown located.');
    
            console.log('Waiting for Vue.js updates...');
            await driver.executeAsyncScript((callback) => {
                const waitForVue = () => {
                    if (window.vue && window.vue.$isServer) {
                        setTimeout(waitForVue, 100);
                    } else {
                        callback();
                    }
                };
                waitForVue();
            });
            console.log('Vue.js updates completed.');
    
            console.log('Clicking on the sort dropdown...');
            const actions = new Actions(driver);
            await actions.move({ origin: sortDropdown }).click().perform();
            console.log('Clicked on the sort dropdown.');

            await driver.sleep(3000);

            const organizationOption = await driver.wait(until.elementLocated(By.xpath("//div[text()='organization name']")), 15000);
            if (await organizationOption.isDisplayed() && await organizationOption.isEnabled()) {
                console.log('Found organization dropdown item. Clicking...');
                await organizationOption.click();
                console.log('Selected "Organization Name" from the sort dropdown.');
            } else {
                console.error('Organization option is not visible or not enabled.');
            }

            await driver.sleep(3000);
            const searchButton = await driver.wait(until.elementLocated(By.id('search-button')), 15000);
            console.log('Found search button.');
            await actions.move({ origin: searchButton }).click().perform();
            console.log('Clicked on the search button.');

            await driver.sleep(3000);

            await driver.wait(until.elementLocated(By.css('#organization-table tbody tr td')), 15000);
            console.log('Table updated after search.');

            const firstRowCells = await driver.findElements(By.css('#organization-table tbody tr td'));
            const [firstCellValue, secondCellValue, thirdCellValue] = await Promise.all([
                firstRowCells[0].getText(),
                firstRowCells[1].getText(),
                firstRowCells[2].getText(),
            ]);
            
            assert.strictEqual(firstCellValue, 'ΤΕΦ', 'First cell value does not match expected value');
            assert.strictEqual(secondCellValue, '0', 'Second cell value does not match expected value');
            assert.strictEqual(thirdCellValue, '1', 'Third cell value does not match expected value');
            
        } catch (error) {
            console.error('Error in the test:', error);
            throw error;
        }
    });
    after(async function () {
        await driver.quit();
    });
});
