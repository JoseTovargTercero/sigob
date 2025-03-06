document.getElementById("formLogin").addEventListener("submit", function (e) {
  e.preventDefault();
  let email = document.getElementsByName("email")[0].value;
  let password = document.getElementsByName("password")[0].value;
  if (email.trim() === "" || password.trim() === "") {
    alert("Por favor, complete todos los campos");
    return;
  } else {
    // enviar datos por post
    fetch("back/sistema_login/login_validate.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        email,
        password,
      }),
    }).then((res) => {
      // manda un alert con el texto imprimido desde login_validate.php
      res.text().then((text) => {
        // convierte el texto de la respuesta a un json
        text = JSON.parse(text);
        if (text.val == true || text.val == "true") {
          location.href = "front/mod_global/global_perfil";
        } else if (text.val == false) {
          toast_s("error", "Error: verifique sus credenciales");
        } else {
          toast_s("error", res.status);
        }
      });
    });
  }
});
