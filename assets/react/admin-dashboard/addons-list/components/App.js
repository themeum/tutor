import React from 'react';
import Header from './Header';
import Filter from './Filter';
import AddonsList from './AddonsList';
import { AddonsContextProvider } from '../context/AddonsContext';

const App = () => {
    return (
        <AddonsContextProvider>
            <main className="tutor-backend-settings-addons-list tutor-dashboard-page">
                <Header />
                <Filter />
                <AddonsList />
            </main>
        </AddonsContextProvider>
    );
}

export default App;