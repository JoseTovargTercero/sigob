async function sendTabulatorData({ tabulatorData }) {
  try {
    const res = await fetch(
      '../../../../../sigob/back/modulo_nomina/tabulador.php',
      {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(tabulatorData),
      }
    )
    console.log(res)
    if (!res.ok) throw { status: res.status, statusText: res.statusText }

    const json = await res.json()
    console.log(json)
  } catch (e) {
    console.error(e)
  }
}

export { sendTabulatorData }
