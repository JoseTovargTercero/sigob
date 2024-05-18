const employeeTable = () => {
  let table = new DataTable('#myTable', {
    data: [
      {
        name: 'Tiger Nixon',
        position: 'System Architect',
        salary: '$3,120',
        start_date: '2011/04/25',
        office: 'Edinburgh',
        extn: 5421,
      },
      {
        name: 'Garrett Winters',
        position: 'Director',
        salary: '5300',
        start_date: '2011/07/25',
        office: 'Edinburgh',
        extn: '8422',
      },
      // ...
    ],
    columns: [
      [{ width: '50%' }, null, null, null, null],
      { data: 'NOMBRE' },
      { data: 'CEDULA' },
      { data: 'office' },
      { data: 'extn' },
      { data: 'start_date' },
      { data: 'salary' },
    ],
  })
}

export { employeeTable }
