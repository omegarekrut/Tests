describe('news', function () {
  describe('view page', function () {
    it('header', function () {
      return this.browser
        .url('/news/view/131467/')
        .execute("document.querySelector('.headerFS').style.position = 'static'")
        .execute("document.querySelector('.headerFS').style.boxShadow = 'none'")
        .assertView('plain', '.contentFS__header', {
          allowViewportOverflow: true,
        });
    });

    it('content', function () {
      return this.browser
        .url('/news/view/131467/')
        .execute("document.querySelector('.headerFS').style.position = 'static'")
        .execute("document.querySelector('.headerFS').style.boxShadow = 'none'")
        .assertView('plain', '.articleFS', {
          ignoreElements: ['iframe'],
          allowViewportOverflow: true,
        });
    });
  });
});
