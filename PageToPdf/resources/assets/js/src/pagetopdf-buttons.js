import axios from 'axios';
import download from 'downloadjs';

const BUTTON_CLASS = 'js-pdf-renderer';
const BUTTON_ATTR = 'data-uri';
const ACTIVE_CLASS = 'js-pdf-renderer--busy';

const bindButtons = () => {
  const buttons = [...document.querySelectorAll(`.${BUTTON_CLASS}`)];
  buttons.forEach(button => {
    button.addEventListener('click', e => {
      e.preventDefault();
      button.classList.add(ACTIVE_CLASS);
      axios({
        method: 'post',
        url: '/!/PageToPdf/generatePDF',
        data: {
          uri: button.getAttribute(BUTTON_ATTR),
          _token: button.getAttribute('data-token')
        }
      })
        .then(({ data }) => {
          if(data.file) {
            download('data:application/pdf;base64,' + data.contents, data.filename, 'application/pdf');
          }
        })
        .catch(err => {
          console.error(err);
        })
        .finally(() => {
          button.classList.remove(ACTIVE_CLASS);
        })
    })
  })
}

document.addEventListener("DOMContentLoaded", bindButtons);
