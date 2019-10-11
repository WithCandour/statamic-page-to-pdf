import axios from 'axios';

const BUTTON_CLASS = 'js-pdf-renderer';
const BUTTON_ATTR = 'data-uri';

const bindButtons = () => {
  const buttons = [...document.querySelectorAll(`.${BUTTON_CLASS}`)];
  buttons.forEach(button => {
    button.addEventListener('click', e => {
      axios({
        method: 'post',
        url: '/!/PageToPdf/generatePDF',
        data: {
          uri: button.getAttribute(BUTTON_ATTR),
          _token: button.getAttribute('data-token')
        }
      })
        .then(response => {
          console.log(response);
        })
        .catch(err => {
          console.error(err);
        })
    })
  })
}

document.addEventListener("DOMContentLoaded", () => {
  bindButtons();
});
