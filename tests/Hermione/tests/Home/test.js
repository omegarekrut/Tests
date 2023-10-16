describe('home', function () {
  describe('list page', function () {
    it('main content', function () {
      return this.browser
        .url('/')
        .execute("document.querySelector('.headerFS').style.position = 'static'")
        .execute("document.querySelector('.headerFS').style.boxShadow = 'none'")
        .assertView('plain', '.contentFS--center', {
          ignoreElements: ['iframe'],
          allowViewportOverflow: true,
        });
    });

    describe('tabs', function () {
      hermione.skip.in('chrome', 'This test work with tab in tablet and mobile');

      it('elements', function () {
        return this.browser
          .url('/')
          .execute("document.querySelector('.headerFS').style.position = 'static'")
          .execute("document.querySelector('.headerFS').style.boxShadow = 'none'")
          .assertView('plain', '.contentFS--home', {
            allowViewportOverflow: true,
          });
      });
    });

    describe('left column', function () {
      describe('desktop', function () {
        hermione.skip.notIn('chrome', 'This test work with left column in desktop');

        it('left column content', function () {
          return this.browser
            .url('/')
            .execute("document.querySelector('.headerFS').style.position = 'static'")
            .execute("document.querySelector('.headerFS').style.boxShadow = 'none'")
            .assertView('plain', '.contentFS--with-aside aside', {
              allowViewportOverflow: true,
            });
        });
      });
    });
  });
});
