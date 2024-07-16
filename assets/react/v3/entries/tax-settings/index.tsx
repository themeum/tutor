import ReactDOM from 'react-dom';

const App = () => {
  return (
    <>
      <h3>Hello</h3>
    </>
  );
};

window.addEventListener('DOMContentLoaded', (e) => {
  function TaxManagement() {
    ReactDOM.render(<App />, document.getElementById('ecommerce_tax'));
  }
  TaxManagement();
});
