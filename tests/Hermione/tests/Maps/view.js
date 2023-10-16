describe('maps', function () {
  describe('view map point', function () {
    it('content', function () {
      return this.browser
        .url('/maps/view/131933/')
        .execute("document.querySelector('.headerFS').style.position = 'static'")
        .execute("document.querySelector('.headerFS').style.boxShadow = 'none'")
        .assertView('plain', '.articleFS', {
          ignoreElements: ['iframe', '.test__hide'],
          allowViewportOverflow: true,
        });
    });

    it('header', function () {
      return this.browser
        .url('/maps/view/131933/')
        .execute("document.querySelector('.headerFS').style.position = 'static'")
        .execute("document.querySelector('.headerFS').style.boxShadow = 'none'")
        .assertView('plain', '.contentFS__header', {
          allowViewportOverflow: true,
        });
    });
  });
});
