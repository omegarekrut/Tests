describe('news', function () {
  describe('list page', function () {
    it('header', function () {
      return this.browser
        .url('/news/')
        .execute("document.querySelector('.headerFS').style.position = 'static'")
        .execute("document.querySelector('.headerFS').style.boxShadow = 'none'")
        .assertView('plain', '.contentFS__header', {
          allowViewportOverflow: true,
        });
    });

    it('main content', function () {
      return this.browser
        .url('/news/')
        .execute("document.querySelector('.headerFS').style.position = 'static'")
        .execute("document.querySelector('.headerFS').style.boxShadow = 'none'")
        .assertView('plain', '.articleFS--list-two-column', {
          allowViewportOverflow: true,
        });
    });
  });
});
