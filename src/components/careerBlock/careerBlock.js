import "./careerBlock.scss";

const url = 'https://apollorise.tech/career.php';

document.addEventListener("DOMContentLoaded", function() {
  const careerBlockForm = document.getElementById('careerBlockForm');
  const policyInput = document.getElementsByName('policy')[0];
  const attachmentLabel = document.getElementById('attachment_label');
  const attachmentInput = document.getElementById('attachment');
  const fileInfo = document.getElementsByClassName('careerBlock__file')[0];
  const clearInputFile = document.getElementsByClassName('careerBlock__removeFile')[0];
  const buttonSubmit = document.getElementById('careerBlockFormSubmit');

  const checkValidForm = () => {
    const fieldsToCheck = ['name', 'email', 'details'];
    let isCanSubmit = true;

    fieldsToCheck.forEach(field => {
      const value = document.getElementsByName(field)[0].value;
      const label = document.querySelector(`.careerBlock__label[for="${field}"]`);

      if (value === "") {
        isCanSubmit = false;
        label.classList.add('error');
      } else {
        label.classList.remove('error');
      }
    });
    return isCanSubmit;
  }

  const submitForm = async () => {
    const isCanSubmit = checkValidForm();
  
    if (isCanSubmit) {
      const headers = new Headers();
      const formData = new FormData(careerBlockForm);
      if(attachmentInput.files[0]) {
        formData.append("attachment", attachmentInput.files[0], attachmentInput.files[0].name);
      }

      headers.append("Accept", "application/json");
  
      const requestOptions = {
        method: 'POST',
        mode: "cors",
        headers,
        body: formData,
        redirect: 'follow'
      };

      try {
        buttonSubmit.disabled = true;
        await fetch(url, requestOptions);
        window.location.replace(`successCareer.html`);
      } catch (e) {
        buttonSubmit.disabled = false;
        alert("Please try again");
      }
    }
  }

  careerBlockForm.addEventListener('submit', async (event) => {
    event.preventDefault();
    await submitForm();
  });

  function preventDefaultDrag(e) {
    e.preventDefault();
    e.stopPropagation();
    attachmentLabel.classList.add("_activeFile");
  }

  function handleDragStart(e) {
    preventDefaultDrag(e);
    attachmentLabel.classList.add("_activeFile");
  }

  function handleDragLeave(e) {
    preventDefaultDrag(e);
    attachmentLabel.classList.remove("_activeFile");
  }

  function handleDrop(e) {
    preventDefaultDrag(e);

    const file = e.dataTransfer.files[0];
    handleUploadedFile(file);
  }

  function handleUploadedFile(file) {
    if (file) {
      if (
        /\.(doc|docx|txt|pdf)$/i.test(file.name) &&
        file.size <= 10 * 1024 * 1024 // 10 MB
      ) {
        fileInfo.getElementsByTagName('p')[0].innerHTML = file.name;
        fileInfo.classList.add('_activeFile');
        attachmentLabel.classList.remove("error");
      } else {
        attachmentLabel.classList.add("error");
        fileInfo.classList.remove('_activeFile');
      }
    }
  }

  attachmentLabel.addEventListener('dragstart', handleDragStart);
  attachmentLabel.addEventListener('dragenter', preventDefaultDrag);
  attachmentLabel.addEventListener('dragover', preventDefaultDrag);
  attachmentLabel.addEventListener('dragleave', handleDragLeave);
  attachmentLabel.addEventListener('drop', handleDrop);

  attachmentLabel.addEventListener('change', (e) => {
    const file = e.target.files[0];
    handleUploadedFile(file);
  });

  policyInput.addEventListener('change', (event) => {
    if (event.currentTarget.checked) {
      buttonSubmit.disabled = false;
    } else {
      buttonSubmit.disabled = true;
    }
  })

  clearInputFile.addEventListener("click", () => {
    fileInfo.classList.remove('_activeFile');
    attachmentInput.value = "";
  })
})
