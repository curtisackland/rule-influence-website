import { describe, it, before, after } from 'mocha';
import assert from 'assert';
import pkg from 'selenium-webdriver';
const { Builder, By, until } = pkg;
import { Actions } from 'selenium-webdriver/lib/input.js';

describe('Selenium Tests for Responses Page', function () {
    let driver;

    before(async function () {
        this.timeout(10000); //give it 10s to load
        driver = await new Builder().forBrowser('chrome').usingServer().build();
    });

    it('Page Load Test', async function () {
        await driver.get('http://localhost:5173/responses');
        const pageTitle = await driver.getTitle();

        assert.equal(pageTitle, 'Rulemaking Influence Explorer');
    });

    it('Display Responses Banner Present', async function () {
      await driver.get('http://localhost:5173/responses');
  
      const commentBanner = await driver.wait(until.elementLocated(By.tagName("h1")));
      const commentText = await commentBanner.getText();
      assert.strictEqual(commentText, 'Responses');
    });

    it('Sort Dropdown Test', async function () {
        this.timeout(10000000);
        try {
            console.log('Navigating to the page...');
            await driver.get('http://localhost:5173/responses');
    
            console.log('Waiting for sort by dropdown to be located...');
            const sortDropdown = await driver.wait(until.elementLocated(By.id('sort-drop-down')), 15000);
            console.log('Sort by dropdown located.');
    
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
    
            console.log('Clicking on the sort by dropdown...');
            const actions = new Actions(driver);
            await actions.move({ origin: sortDropdown }).click().perform();
            console.log('Clicked on the sort dropdown.');

            await driver.sleep(3000);

            const numberOfChangesOption = await driver.wait(until.elementLocated(By.xpath("//div[text()='Number of Linked Comments']")), 15000);
            if (await numberOfChangesOption.isDisplayed() && await numberOfChangesOption.isEnabled()) {
                console.log('Found Number of Linked Comments dropdown item. Clicking...');
                await numberOfChangesOption.click();
                console.log('Selected "Number of Linked Comments" from the sort dropdown.');
            } else {
                console.error('Number of Linked Comments option is not visible or not enabled.');
            }

            await driver.sleep(3000);
            const submitButton = await driver.wait(until.elementLocated(By.id('submit-button')), 15000);
            console.log('Found submit button.');
            await actions.move({ origin: submitButton }).click().perform();
            console.log('Clicked on the submit button.');

            await driver.sleep(3000);

            const responseCards = await driver.findElements(By.css('#response-id'));
            assert.strictEqual(responseCards.length, 10);
            const [firstResponseId, secondResponseId, thirdResponseId] = await Promise.all([
                responseCards[0].getText(),
                responseCards[1].getText(),
                responseCards[2].getText(),
            ]);
            
            assert.strictEqual(firstResponseId, 'Response ID: 48');
            assert.strictEqual(secondResponseId, 'Response ID: 27');
            assert.strictEqual(thirdResponseId, 'Response ID: 3');
            
        } catch (error) {
            console.error('Error in the test:', error);
            throw error;
        }
    });

    after(async function () {
        await driver.quit();
    });
});
