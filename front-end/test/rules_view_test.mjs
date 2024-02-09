import { describe, it, before, after } from 'mocha';
import assert from 'assert';
import pkg from 'selenium-webdriver';
const { Builder, By, until } = pkg;
import { Actions } from 'selenium-webdriver/lib/input.js';



describe('Selenium Tests for Rules Page', function () {
    let driver;
    async function loadRulesPage() {
        await driver.get('http://localhost:5173/rules');
    }

    before(async function () {
        this.timeout(10000); //give it 10s to load
        driver = await new Builder().forBrowser('chrome').usingServer().build();
    });

    describe('Basic page load tests', async function () {
        before(async function() {
            await loadRulesPage();
        });

        it('Page Load Test', async function () {
            await loadRulesPage();
            const pageTitle = await driver.getTitle();

            assert.equal(pageTitle, 'Rulemaking Influence Explorer');
        });

        it('Header present', async function () {
            const header = await driver.wait(until.elementLocated(By.xpath('/html/body/div/div/div/main/div/div[1]/h1')))
            const headerText = await header.getText();
            assert.equal(headerText, 'Rules');
        });

        it('Nav bar present', async function () {

            const bar = await driver.wait(until.elementLocated(By.tagName("header")), 10000);

            assert(bar != null);
        });

        it('Start date default to 2000-01-01', async function () {

            const filterStartDate = await driver.wait(until.elementLocated(By.id("filter-date-start")), 10000);
            const dateValue = await filterStartDate.getAttribute("value");
            assert(dateValue === "2000-01-01");
        });

        it('End date default to empty', async function () {
            const filterStartDate = await driver.wait(until.elementLocated(By.id("filter-date-end")), 10000);
            const dateValue = await filterStartDate.getAttribute("value");
            assert(dateValue === "");
        });
    })

    describe('Search by id', async function () {
        let cardId = "99-33595";
        before(async function() {
            await loadRulesPage();
        });

        it('Run search', async function () {
            this.timeout(100000);
            const idTextBox = await driver.wait(until.elementLocated(By.id("filter-id-title")), 10000);
            idTextBox.sendKeys(cardId);

            const searchButton = await driver.wait(until.elementLocated(By.id("search-button")), 10000);
            await searchButton.click();
            await driver.sleep(3000);
        });

        it('Find result', async function (){
            this.timeout(100000);

            const firstCard = await driver.wait(until.elementLocated(By.css(".v-card-title.rule-id")), 10000);
            const cardSearchId = await firstCard.getText();
            assert(cardSearchId !== null);
        });
    });

    describe('Search by title', async function () {
        let cardId = "Minimum Wages for Federal and Federally Assisted Construction; General Wage Determination Decisions\n";
        before(async function(){
            await loadRulesPage();
        });

        it('Run search', async function () {
            this.timeout(100000);
            const idTextBox = await driver.wait(until.elementLocated(By.id("filter-id-title")), 10000);
            idTextBox.sendKeys(cardId);

            const searchButton = await driver.wait(until.elementLocated(By.id("search-button")), 10000);
            await searchButton.click();
            await driver.sleep(3000);
        });

        it('Find result', async function (){
            this.timeout(100000);

            const firstCard = await driver.wait(until.elementLocated(By.css(".v-card-title.rule-title")), 10000);
            const cardSearchId = await firstCard.getText();
            assert(cardSearchId !== null);
        });
    });


    after(async function () {
        await driver.quit();
    });
});
