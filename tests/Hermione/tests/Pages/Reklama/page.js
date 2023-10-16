describe('pages', function () {
  describe('reklama', function () {
    it('content', function () {
      return this.browser
        .url('/reklama/')
        .execute("document.querySelector('.headerFS').style.position = 'static'")
        .execute("document.querySelector('.headerFS').style.boxShadow = 'none'")
        .assertView('plain', '.ads', {
          allowViewportOverflow: true,
        });
    });
  });
});
