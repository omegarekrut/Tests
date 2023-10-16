describe('pages', function () {
  describe('rules', function () {
    it('header', function () {
      return this.browser
        .url('/rules/')
        .execute("document.querySelector('.headerFS').style.position = 'static'")
        .execute("document.querySelector('.headerFS').style.boxShadow = 'none'")
        .assertView('plain', '.contentFS__header', {
          allowViewportOverflow: true,
        });
    });

    it('content', function () {
      return this.browser
        .url('/rules/')
        .execute("document.querySelector('.headerFS').style.position = 'static'")
        .execute("document.querySelector('.headerFS').style.boxShadow = 'none'")
        .assertView('plain', '.articleFS__content', {
          allowViewportOverflow: true,
        });
    });
  });
});
