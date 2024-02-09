import { describe, it, before, after } from 'mocha';
import assert from 'assert';
import pkg from 'selenium-webdriver';
const { Builder, By, until } = pkg;
import { Actions } from 'selenium-webdriver/lib/input.js';

describe('Selenium Tests for Comments Page', function () {
    let driver;

    before(async function () {
        this.timeout(10000); //give it 10s to load
        driver = await new Builder().forBrowser('chrome').usingServer().build();
    });

    it('Page Load Test', async function () {
        await driver.get('http://localhost:5173/comments');
        const pageTitle = await driver.getTitle();

        assert.equal(pageTitle, 'Rulemaking Influence Explorer');
    });

    it('Display Comments Banner Present', async function () {
      await driver.get('http://localhost:5173/comments');
  
      const commentBanner = await driver.wait(until.elementLocated(By.tagName("h1")));
      const commentText = await commentBanner.getText();
      assert.strictEqual(commentText, 'Comments');
    });

    it('Sort Dropdown Test', async function () {
        this.timeout(10000000);
        try {
            console.log('Navigating to the page...');
            await driver.get('http://localhost:5173/comments');
    
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

            const numberOfChangesOption = await driver.wait(until.elementLocated(By.xpath("//div[text()='None']")), 15000);
            if (await numberOfChangesOption.isDisplayed() && await numberOfChangesOption.isEnabled()) {
                console.log('Found none dropdown item. Clicking...');
                await numberOfChangesOption.click();
                console.log('Selected "None" from the sort dropdown.');
            } else {
                console.error('None option is not visible or not enabled.');
            }

            await driver.sleep(3000);
            const submitButton = await driver.wait(until.elementLocated(By.id('submit-button')), 15000);
            console.log('Found submit button.');
            await actions.move({ origin: submitButton }).click().perform();
            console.log('Clicked on the submit button.');

            await driver.sleep(3000);

            const commentCards = await driver.findElements(By.css('#comment-id'));
            assert.strictEqual(commentCards.length, 10);
            const [firstCommentId, secondCommentId, thirdCommentId] = await Promise.all([
                commentCards[0].getText(),
                commentCards[1].getText(),
                commentCards[2].getText(),
            ]);
            
            assert.strictEqual(firstCommentId, 'Comment ID: ACF-2009-0001-0003');
            assert.strictEqual(secondCommentId, 'Comment ID: ACF-2009-0001-0004');
            assert.strictEqual(thirdCommentId, 'Comment ID: ACF-2009-0001-0005');
            
        } catch (error) {
            console.error('Error in the test:', error);
            throw error;
        }
    });

    after(async function () {
        await driver.quit();
    });
});
