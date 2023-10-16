describe('pages', function () {
  describe('souv', function () {
    it('header', function () {
      return this.browser
        .url('/pages/souv/')
        .execute("document.querySelector('.headerFS').style.position = 'static'")
        .execute("document.querySelector('.headerFS').style.boxShadow = 'none'")
        .assertView('plain', '.contentFS__header', {
          allowViewportOverflow: true,
        });
    });

    it('content', function () {
      return this.browser
        .url('/pages/souv/')
        .execute("document.querySelector('.headerFS').style.position = 'static'")
        .execute("document.querySelector('.headerFS').style.boxShadow = 'none'")
        .assertView('plain', '.articleFS__content', {
          allowViewportOverflow: true,
        });
    });
  });
});
