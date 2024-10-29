const getAlgo = async () => {
  showLoader()
  try {
    let res = await fetch(ejercicioFiscalUrl, {
      method: 'POST',
      body: JSON.stringify({ accion: 'obtener_todos' }),
    })

    if (!res.ok) throw { status: res.status, statusText: res.statusText }

    // const clone = res.clone()

    // let text = await clone.text()

    // console.log(text)

    const json = await res.json()

    // console.log(json)
    if (json.success) {
      if (id) {
        return json.success
      }
      let mappedData = mapData({
        obj: json.success,
        name: 'ano',
        id: 'id',
      })

      return { mappedData, fullInfo: json.success }
    }

    if (json.error) {
      toastNotification({ type: NOTIFICATIONS_TYPES.fail, message: json.error })
    }
    return json
  } catch (e) {
    console.log(e)

    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Error al obtener algo',
    })
  } finally {
    hideLoader()
  }
}
