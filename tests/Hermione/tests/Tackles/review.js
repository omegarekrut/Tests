describe('tackles', function () {
  describe('review page', function () {
    it('content', function () {
      return this.browser
        .url('/tackles/review/131702/')
        .assertView('plain', '.tackles', {
          ignoreElements: ['iframe'],
          allowViewportOverflow: true,
        });
    });
  });
});
