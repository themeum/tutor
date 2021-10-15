import React from 'react';
import Header from './Header';
import Filter from './Filter';
import AddonsList from './AddonsList';

const App = () => {
    return (
        <main className="tutor-backend-settings-addons-list tutor-dashboard-page">
            <Header />
            <Filter />
            <AddonsList />
        </main>
    );
}

export default App;