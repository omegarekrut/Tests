[131997, 131979, 131944, 131910].forEach((id) => {
  describe('tidings', function () {
    describe(`tidings ${id} view page`, function () {
      it('header', function () {
        return this.browser
          .url(`/tidings/view/${id}/`)
          .execute("document.querySelector('.headerFS').style.position = 'static'")
          .execute("document.querySelector('.headerFS').style.boxShadow = 'none'")
          .assertView('plain', '.contentFS__header', {
            allowViewportOverflow: true,
          });
      });

      it('content', function () {
        return this.browser
          .url(`/tidings/view/${id}/`)
          .execute("document.querySelector('.headerFS').style.position = 'static'")
          .execute("document.querySelector('.headerFS').style.boxShadow = 'none'")
          .assertView('plain', '.articleFS', {
            ignoreElements: ['iframe'],
            allowViewportOverflow: true,
          });
      });
    });
  });
});
