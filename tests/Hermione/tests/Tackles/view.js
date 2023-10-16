describe('tackles', function () {
  describe('view page', function () {
    it('content', function () {
      return this.browser
        .url('/tackles/view/131550/')
        .assertView('plain', '.sidecol-content', {
          ignoreElements: ['iframe'],
          allowViewportOverflow: true,
        });
    });
  });
});
