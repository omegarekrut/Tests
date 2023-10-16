describe('gallery', function () {
  describe('list page', function () {
    it('main content', function () {
      return this.browser
        .url('/gallery/')
        .execute("document.querySelector('.headerFS').style.position = 'static'")
        .execute("document.querySelector('.headerFS').style.boxShadow = 'none'")
        .assertView('plain', '.articles-page-list', {
          ignoreElements: ['iframe'],
          allowViewportOverflow: true,
        });
    });

    describe('tabs', function () {
      hermione.skip.in('chrome', 'This test work with tab in tablet and mobile');

      it('elements', function () {
        return this.browser
          .url('/gallery/')
          .execute("document.querySelector('.headerFS').style.position = 'static'")
          .execute("document.querySelector('.headerFS').style.boxShadow = 'none'")
          .assertView('plain', '.contentFS__tabs', {
            allowViewportOverflow: true,
          });
      });
    });

    describe('left column', function () {
      describe('tablet and mobile', function () {
        hermione.skip.in('chrome', 'This test work with left column in tablet and mobile');

        it('left column content', function () {
          return this.browser
            .url('/gallery/')
            .execute("document.querySelector('.headerFS').style.position = 'static'")
            .execute("document.querySelector('.headerFS').style.boxShadow = 'none'")
            .click('.contentFS__tabs a:last-child')
            .assertView('plain', '#rubricTab', {
              allowViewportOverflow: true,
            });
        });
      });

      describe('desktop', function () {
        hermione.skip.notIn('chrome', 'This test work with left column in desktop');

        it('left column content', function () {
          return this.browser
            .url('/gallery/')
            .execute("document.querySelector('.headerFS').style.position = 'static'")
            .execute("document.querySelector('.headerFS').style.boxShadow = 'none'")
            .assertView('plain', '#rubricTab', {
              allowViewportOverflow: true,
            });
        });
      });
    });
  });
});
