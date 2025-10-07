// ----- Variables globales -----
const steps = document.querySelectorAll('.step');
let currentStep = 0;

function showStep(n) {
  steps.forEach((step, i) => step.classList.toggle('active', i === n));
  currentStep = n;
}

// Función para validar campos y marcar en rojo
function validateFields(fields) {
  let valid = true;
  fields.forEach(field => {
    if (field.type === "checkbox") {
      if (!field.checked) {
        field.style.outline = "2px solid red";
        valid = false;
      } else {
        field.style.outline = "";
      }
    } else if (field.type === "file") {
      if (!field.files[0]) {
        field.style.outline = "2px solid red";
        valid = false;
      } else {
        field.style.outline = "";
      }
    } else {
      if (!field.value.trim()) {
        field.style.outline = "2px solid red";
        valid = false;
      } else {
        field.style.outline = "";
      }
    }
  });
  return valid;
}

// ----- Paso 1: Datos personales -----
document.getElementById('next1').addEventListener('click', () => {
  const email = document.querySelector('input[name="email"]');
  const pass = document.getElementById('password');
  const confirm = document.getElementById('confirm');
  const telefono = document.querySelector('input[name="telefono"]');

  // Validar campos
  if (!validateFields([email, pass, confirm, telefono])) return;

  // Validar coincidencia de contraseñas
  if (pass.value.trim() !== confirm.value.trim()) {
    alert("Las contraseñas no coinciden.");
    pass.style.outline = "2px solid red";
    confirm.style.outline = "2px solid red";
    return;
  } else {
    pass.style.outline = "";
    confirm.style.outline = "";
  }

  showStep(1); // Avanza al paso 2
});

// ----- Paso 2: Carnet -----
document.getElementById('next2').addEventListener('click', () => {
  const carnet = document.querySelector('input[name="carnet"]');
  if (!validateFields([carnet])) return;
  showStep(2);
});

// ----- Paso 3: DNI -----
document.getElementById('next3').addEventListener('click', () => {
  const dni = document.querySelector('input[name="dni"]');
  if (!validateFields([dni])) return;
  showStep(3);
  startCamera();
});

// ----- Paso 4: Checkbox condiciones -----
document.querySelector('form').addEventListener('submit', (e) => {
  const checkbox = document.querySelector('input[name="condiciones"]');
  if (!validateFields([checkbox])) {
    e.preventDefault(); // evita enviar el formulario
  }
});

// Botones de volver
document.getElementById('back2').addEventListener('click', () => showStep(0));
document.getElementById('back3').addEventListener('click', () => showStep(1));
document.getElementById('back4').addEventListener('click', () => showStep(2));

// ----- Cámara y foto -----
const video = document.getElementById('video');
const canvas = document.getElementById('canvas');
const captureBtn = document.getElementById('capture');
const fotoInput = document.getElementById('foto');

function startCamera() {
  if (!video.srcObject) {
    navigator.mediaDevices.getUserMedia({ video: true })
      .then(stream => video.srcObject = stream)
      .catch(err => alert("Error al acceder a la cámara: " + err));
  }
}

// Capturar foto
captureBtn.addEventListener('click', () => {
  const ctx = canvas.getContext('2d');
  canvas.width = video.videoWidth;
  canvas.height = video.videoHeight;
  ctx.drawImage(video, 0, 0);
  const dataUrl = canvas.toDataURL('image/png');
  fotoInput.value = dataUrl;
  alert("Foto capturada correctamente.");
});

function goToLogin() {
    window.location.href = '../login/login.html';
}

// Mostrar nombre de archivo seleccionado
const carnetInput = document.getElementById('carnet');
const carnetName = document.getElementById('carnet-name');
if (carnetInput) {
  carnetInput.addEventListener('change', () => {
    const f = carnetInput.files[0];
    carnetName.textContent = f ? f.name : 'No seleccionado';
  });
}

const dniInput = document.getElementById('dni');
const dniName = document.getElementById('dni-name');
if (dniInput) {
  dniInput.addEventListener('change', () => {
    const f = dniInput.files[0];
    dniName.textContent = f ? f.name : 'No seleccionado';
  });
}