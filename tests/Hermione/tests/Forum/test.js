describe('forum', function () {
  it('forum content', function () {
    return this.browser
      .url('/forum')
      .execute("document.querySelector('.headerFS').style.position = 'static'")
      .execute("document.querySelector('.headerFS').style.boxShadow = 'none'")
      .assertView('plain', '.forum-style', {
        ignoreElements: ['.fs__forum__banner'],
        allowViewportOverflow: true,
      });
  });
});
