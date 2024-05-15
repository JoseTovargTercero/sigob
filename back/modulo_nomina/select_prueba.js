import React, { useState, useRef, useEffect } from 'react';
import './App.css';

function App() {
  const [filter, setFilter] = useState('');
  const [showFilter, setShowFilter] = useState(false);
  const selectRef = useRef(null);
  const filterInputRef = useRef(null);

  const options = [
    { value: 1, label: 'Option 1' },
    { value: 2, label: 'Option 2' },
    { value: 3, label: 'Option 3' },
    { value: 4, label: 'Option 4' },
    { value: 5, label: 'Option 5' },
    // Agrega más opciones aquí
  ];

  const handleFocus = () => {
    setShowFilter(true);
    setTimeout(() => {
      filterInputRef.current.focus();
    }, 100); // Pequeño retraso para asegurar el enfoque
  };

  const handleBlur = () => {
    setTimeout(() => {
      setShowFilter(false);
    }, 200); // Retraso para permitir la selección de opciones
  };

  const filteredOptions = options.filter(option =>
    option.label.toLowerCase().includes(filter.toLowerCase())
  );

  return (
    <div className="App">
      <div
        id="filterContainer"
        style={{ display: showFilter ? 'block' : 'none' }}
      >
        <input
          type="text"
          id="filterInput"
          placeholder="Escribe para filtrar..."
          value={filter}
          onChange={e => setFilter(e.target.value)}
          ref={filterInputRef}
          onBlur={handleBlur}
        />
      </div>
      <select
        id="mySelect"
        size="10"
        onFocus={handleFocus}
        onBlur={handleBlur}
        ref={selectRef}
      >
        {filteredOptions.map(option => (
          <option key={option.value} value={option.value}>
            {option.label}
          </option>
        ))}
      </select>
    </div>
  );
}

export default App;

#filterContainer {
  margin-bottom: 10px;
}

#filterInput {
  width: 100%;
}

#mySelect {
  width: 100%;
}