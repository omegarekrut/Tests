describe('maps', function () {
  describe('list maps', function () {
    it('content', function () {
      return this.browser
        .url('/maps/')
        .execute("document.querySelector('.headerFS').style.position = 'static'")
        .execute("document.querySelector('.headerFS').style.boxShadow = 'none'")
        .assertView('plain', '.maps-list', {
          ignoreElements: ['iframe', '.test__hide'],
          allowViewportOverflow: true,
        });
    });

    it('header', function () {
      return this.browser
        .url('/maps/')
        .execute("document.querySelector('.headerFS').style.position = 'static'")
        .execute("document.querySelector('.headerFS').style.boxShadow = 'none'")
        .assertView('plain', '.contentFS__header', {
          allowViewportOverflow: true,
        });
    });
  });
});
