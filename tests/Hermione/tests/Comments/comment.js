/* eslint-disable no-undef */
/* eslint-disable prefer-arrow-callback */
/* eslint-disable func-names */
/* eslint-disable space-before-function-paren */
const articleViewUrl = '/articles/view/149296/';

describe('comments', function () {
  it('best', async function () {
    await this.browser.url(articleViewUrl);
    await this.browser.execute("document.querySelector('.headerFS').style.position = 'static'");
    await this.browser.execute("document.querySelector('.headerFS').style.boxShadow = 'none'");
    await this.browser.assertView('plain', '.commentsFS--best', {
      allowViewportOverflow: true,
    });
  });

  it('normal', async function () {
    await this.browser.url(articleViewUrl);
    await this.browser.execute("document.querySelector('.headerFS').style.position = 'static'");
    await this.browser.execute("document.querySelector('.headerFS').style.boxShadow = 'none'");
    await this.browser.assertView('plain', '.js-comments', {
      allowViewportOverflow: true,
    });
  });

  it('auth-block', async function () {
    await this.browser.url(articleViewUrl);
    await this.browser.execute("document.querySelector('.headerFS').style.position = 'static'");
    await this.browser.execute("document.querySelector('.headerFS').style.boxShadow = 'none'");
    await this.browser.assertView('plain', '.comment-auth-block');
    await this.browser.elementClick('buttonLog');
    await this.browser.assertView('click', '.comment-auth-block', {
      allowViewportOverflow: true,
    });
  });
});
