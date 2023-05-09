(function () {
    const url = '/data.php';
    const interval = 1000000;
    const tableTemplate = `
    <table>
      <thead>
        <tr>
        <td class="group-row" colspan="2" style="text-align:left"><h3>Ubuntu Real-Time Docker Containers and System Monitoring on Website</h3></td>
        </tr>
        <tr>
          <th>Property</th>
          <th>Value</th>
        </tr>
      </thead>
      <tbody>
        {{rows}}
      </tbody>
    </table>
  `;

    const rowTemplate = `
    <tr>
      <td>{{property}}</td>
      <td>{{value}}</td>
    </tr>
  `;

    const groupTemplate = `
    <tr>
      <td class="group-row" colspan="2">{{group}}</td>
    </tr>
  `;

    function generateRows(data, parent = '') {
        let rows = '';

        for (const [key, value] of Object.entries(data)) {
            const property = parent ? `${parent} -> ${key}` : key;
            if (typeof value === 'object' && value !== null) {
                const groupRow = groupTemplate.replace('{{group}}', property);
                rows += groupRow;
                rows += generateRows(value, property);
            } else {
                const valueString = JSON.stringify(value).replace(/"/g, '').replace(/\\/g, "");
                const propertyRow = rowTemplate.replace('{{property}}', property).replace('{{value}}', valueString);
                rows += propertyRow;
            }
        }

        return rows;
    }

    function fetchData() {
        fetch(url)
                .then(response => response.json())
                .then(data => {
                    const rows = generateRows(data);
                    const table = tableTemplate.replace('{{rows}}', rows);
                    const rootElement = document.getElementById('root');
                    rootElement.innerHTML = table;
                })
                .catch(error => {
                    console.error('Error:', error);
                });
    }
    fetchData();
    setInterval(fetchData, interval);
})();
