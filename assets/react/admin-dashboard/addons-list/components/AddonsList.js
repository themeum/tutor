import React from 'react';

const AddonsList = () => {
    return (
        <div classNameName="tutor-addons-list-items">
            <div className="tutor-addons-card">
             <div className="card-body tutor-px-30 tutor-py-40">
                 <div className="addon-logo">
                    <img src="" alt="" />  
                 </div>
                 <div className="addon-title tutor-mt-20">
                     <h5 className="text-medium-h5 color-text-primary"></h5>
                     <p className="text-medium-small color-text-hints tutor-mt-5">
                         By <a href="" className="color-brand-wordpress"></a>
                     </p>
                 </div>
                 <div className="addon-des text-regular-body color-text-subsued tutor-mt-20">
                     <p></p>
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
                    <label className="tutor-form-toggle">
                        <input type="checkbox" className="tutor-form-toggle-input" />
                        <span className="tutor-form-toggle-control"></span>
                        <span className="tutor-form-toggle-label color-text-primary tutor-ml-5">Active</span>
                    </label>
                 </div>
                 <div className="addon-version text-medium-small color-text-hints">
                     Version : <span className="text-bold-small color-text-primary"></span>
                 </div>
             </div>
         </div>
        </div>
    );
}

export default AddonsList;