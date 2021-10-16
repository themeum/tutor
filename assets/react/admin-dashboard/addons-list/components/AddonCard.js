import React, { Fragment, useState } from 'react';

const AddonCard = ({addon}) => {
    //let isChecked = addon.is_enabled && true;
    const author = 'Themeum';
    const url = 'https://www.themeum.com';
    const isSubscribed = false;

    const [isChecked, setIsChecked] = useState( addon.is_enabled );

    const handleOnChange = (event) => {
        const value = event.target.checked;
        setIsChecked(value);
    }
    
    return (
        <div className={`tutor-addons-card ${isSubscribed ? 'not-subscribed' : ''}`}>
             <div className="card-body tutor-px-30 tutor-py-40">
                 <div className="addon-logo">
                    <img src={addon.thumb_url} alt={addon.name} />  
                 </div>
                 <div className="addon-title tutor-mt-20">
                     <h5 className="text-medium-h5 color-text-primary">{addon.name}</h5>
                     <p className="text-medium-small color-text-hints tutor-mt-5">
                         By <a href={url} className="color-brand-wordpress">{author}</a>
                     </p>
                 </div>
                 <div className="addon-des text-regular-body color-text-subsued tutor-mt-20">
                     <p>{addon.description}</p>
                 </div>
             </div>
             <div
                 className="
                     card-footer
                     tutor-px-30 tutor-py-25
                     d-flex
                     justify-content-between
                     align-items-center
                 "
             >
                 <div className="addon-toggle">
                 {isSubscribed ? 
                    <Fragment>
                        <p className="color-text-hints text-medium-small">Required Plugin(s)</p>
                        <p className="color-text-primary text-medium-caption tutor-mt-2">
                            Woocommerce Subscription
                        </p>
                    </Fragment>
                        : 
                    <Fragment>
                        <label className="tutor-form-toggle">
                            <input type="checkbox" className="tutor-form-toggle-input" checked={isChecked} onChange={handleOnChange} />
                            <span className="tutor-form-toggle-control"></span>
                            <span className="tutor-form-toggle-label color-text-primary tutor-ml-5">Active</span>
                        </label>
                    </Fragment>
                }
                </div>
                <div className="addon-version text-medium-small color-text-hints">
                    Version : <span className="text-bold-small color-text-primary">{addon.version}</span>
                </div>
            </div>
        </div>
    );
}

export default AddonCard;