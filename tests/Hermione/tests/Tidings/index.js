/*
* @todo h1 + search form
*/

describe('tidings', function () {
  describe('list page', function () {
    it('list content', function () {
      return this.browser
        .url('/tidings/')
        .execute("document.querySelector('.headerFS').style.position = 'static'")
        .execute("document.querySelector('.headerFS').style.boxShadow = 'none'")
        .assertView('plain', '.tidings__list', {
          ignoreElements: ['iframe', '.tidings__list-promo'],
          allowViewportOverflow: true,
        });
    });
  });
});
