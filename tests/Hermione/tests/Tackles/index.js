/*
 * @todo separate left column
 */
describe('tackles', function () {
  describe('list page', function () {
    it('main content', function () {
      return this.browser
        .url('/tackles/')
        .assertView('plain', '.sidecol-content', {
          ignoreElements: ['iframe'],
          allowViewportOverflow: true,
        });
    });

    it('left column', function () {
      return this.browser
        .url('/tackles/')
        .assertView('plain', '.sidecol-column', {
          ignoreElements: ['.tackle-list__item'],
          allowViewportOverflow: true,
        });
    });
  });
});
