/* eslint-disable no-undef */
/* eslint-disable prefer-arrow-callback */
/* eslint-disable func-names */
/* eslint-disable space-before-function-paren */

const url = '/articles/';
const viewUrl = '/articles/view/149296/';

describe('breadcrumbs', function () {
  describe('on symfony pages', function () {
    it('index', async function () {
      await this.browser.url(url);
      await this.browser.execute("document.querySelector('.headerFS').style.position = 'static'");
      await this.browser.execute("document.querySelector('.headerFS').style.boxShadow = 'none'");
      await this.browser.assertView('plain', '.breadcrumbs', {
        allowViewportOverflow: true,
      });
    });

    it('view', async function () {
      await this.browser.url(viewUrl);
      await this.browser.elementClick('.articles-page-list section:first-child .articleFS__content__link');
      await this.browser.execute("document.querySelector('.headerFS').style.position = 'static'");
      await this.browser.execute("document.querySelector('.headerFS').style.boxShadow = 'none'");
      await this.browser.getElementText('Главная');
      await this.browser.assertView('plain', '.breadcrumbs', {
        allowViewportOverflow: true,
      });
    });
  });
});
