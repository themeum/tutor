import React from "react";

export default () => {

    return (
        <div>
            <h4>Modal</h4>

            // modal essential elements
            <br/>

            <div className="row">
                <div className="col-6">
                    <div style={{ padding: 10, border: '1px solid #ddd'}}>
                        <div className="tutor-modal-steps">
                            <ul>
                                <li className="tutor-is-completed">
                                    <span>Quiz Info</span>
                                    <button className="tutor-modal-step-btn">1</button>
                                </li>
                                <li className="tutor-is-completed">
                                    <span>Question</span>
                                    <button className="tutor-modal-step-btn">2</button>
                                </li>
                                <li className="tutor-is-completed">
                                    <span>Opt</span>
                                    <button className="tutor-modal-step-btn">3</button>
                                </li>
                                <li>
                                    <span>Settings</span>
                                    <button className="tutor-modal-step-btn">4</button>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <br/>


            <div className="row">
                <div className="col-4">
                    // attachments
                    <div className="tutor-attachment tutor-mb-5">
                        <h6 className="tutor-attachment-name">
                            <span>My assignment.zip My assignment.zip My assignment.zip My assignment.zip My assignment.zip My assignment.zip My assignment.zip My assignment.zip My assignment.zip My assignment.zip My assignment.zip My assignment.zip My assignment.zip</span>
                        </h6>
                        <div className="tutor-attachment-right">
                            <span className="tutor-attachment-size">Size: 15.56 KB</span>
                            <button className="tutor-attachment-delete">
                                <span className="fas fa-times"></span>
                            </button>
                        </div>
                    </div>
                    <div className="tutor-attachment tutor-mb-20">
                        <h6 className="tutor-attachment-name">
                            <span>My assignment.zip</span>
                        </h6>
                        <div className="tutor-attachment-right">
                            <span className="tutor-attachment-size">Size: 15.56 KB</span>
                            <button className="tutor-attachment-delete">
                                <span className="fas fa-times"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <br/>
            <br/>
            
            <div className="row">
                <div className="col-6">
                    // radio with content
                    <label className="tutor-radio-select tutor-mb-10" form="radio_aa">
                        <input className="tutor-form-check-input" type="radio" name="radio_aa" id="radio_aa"/>
                        <div className="tutor-radio-select-content">
                            <span className="tutor-radio-select-title">Retry Mode</span>
                            Unlimited attempts on each question. <a href="#">Live Demo</a>
                        </div>
                    </label>
                    <label className="tutor-radio-select tutor-mb-10" form="radio_aa">
                        <input className="tutor-form-check-input" type="radio" name="radio_aa" id="radio_aa"/>
                        <div className="tutor-radio-select-content">
                            <span className="tutor-radio-select-title">Retry Mode</span>
                            Unlimited attempts on each question. <a href="#">Live Demo</a>
                        </div>
                    </label>
                    <label className="tutor-radio-select" form="radio_aa">
                        <input className="tutor-form-check-input" type="radio" name="radio_aa" id="radio_aa"/>
                        <div className="tutor-radio-select-content">
                            <span className="tutor-radio-select-title">Retry Mode</span>
                            Unlimited attempts on each question. <a href="#">Live Demo</a>
                        </div>
                    </label>
                </div>
            </div>

            <br/>
            <br/>

            <div className="row">
                <div className="col-6">
                    // quiz-builder-question

                    <div className="tutor-quiz-item tutor-mb-15" draggable>
                        <div className="tutor-quiz-item-label">
                            <span className="tutor-quiz-item-draggable fas fa-bars"></span>
                            <h6 className="tutor-quiz-item-name">Popular Uses Of The Internet?</h6>
                        </div>
                        <div className="tutor-quiz-item-action">
                            <div className="tutor-quiz-item-type">
                                <img src="https://i.imgur.com/jDf13Ml.jpg" alt=""/>
                                True or False
                            </div>
                            <button>
                                <i className="fas fa-ellipsis-v"></i>
                            </button>
                        </div>
                    </div>
                    <div className="tutor-quiz-item tutor-mb-15" draggable>
                        <div className="tutor-quiz-item-label">
                            <span className="tutor-quiz-item-draggable fas fa-bars"></span>
                            <h6 className="tutor-quiz-item-name">Uniquely pursue interdependent metrics via process-centric e-commerce. Energistically whiteboard.</h6>
                        </div>
                        <div className="tutor-quiz-item-action">
                            <div className="tutor-quiz-item-type">
                                <img src="https://i.imgur.com/Zl39u6F.jpg" alt=""/>
                                Sorting
                            </div>
                            <button>
                                <i className="fas fa-ellipsis-v"></i>
                            </button>
                        </div>
                    </div>

                </div>
            </div>

            <br/>



            <div className="modal-showcase" style={{ background: "#222222", padding: 50}}>
                <div className="row">
                    <div className="col">
                        <div data-tutor-modal-target="modal-05" className="tutor-btn">Open modal</div>
                        <div id="modal-05" className="tutor-modal">
                            <span className="tutor-modal-overlay"></span>
                            <button data-tutor-modal-close className="tutor-modal-close">
                                <span className="las la-times"></span>
                            </button>
                            <div className="tutor-modal-root">
                                <div className="tutor-modal-inner">
                                    <div className="tutor-modal-body tutor-text-center">
                                        <div className="tutor-modal-icon">
                                            <img src="https://i.imgur.com/Nx6U2u7.png" alt=""/>
                                        </div>
                                        <div className="tutor-modal-text-wrap">
                                            <h3 className="tutor-modal-title">Delete This Course?</h3>
                                            <p>Are you sure you want to delete this course permanently from the site? Please confirm your choice.</p>
                                        </div>
                                        <div className="tutor-modal-btns tutor-btn-group">
                                            <button data-tutor-modal-close className="tutor-btn tutor-is-outline tutor-is-default">
                                                Cancel
                                            </button>
                                            <button className="tutor-btn">
                                                Yes, Delete Course
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <br/>
                <br/>
                <div className="row">
                    <div className="col">
                        <div data-tutor-modal-target="modal-04" className="tutor-btn">Open modal</div>
                        <div id="modal-04" className="tutor-modal tutor-is-sm">
                            <span className="tutor-modal-overlay"></span>
                            <button data-tutor-modal-close className="tutor-modal-close">
                                <span className="las la-times"></span>
                            </button>
                            <div className="tutor-modal-root">
                                <div className="tutor-modal-inner">
                                    <div className="tutor-modal-body">
                                        <h3 className="tutor-modal-title tutor-mb-30">Hi, Welcome back!</h3>
                                        <form action="#">
                                            <div className="tutor-input-group tutor-form-control-has-icon-right tutor-mb-20">
                                                <input type="text" className="tutor-form-control" placeholder="Username or Email Id"/>
                                                {/*<span className="las la-calendar-alt tutor-input-group-icon-right"></span>*/}
                                            </div>
                                            <div className="tutor-input-group tutor-form-control-has-icon-right tutor-mb-30">
                                                <input type="password" className="tutor-form-control" placeholder="Password"/>
                                                {/*<span className="las la-calendar-alt tutor-input-group-icon-right"></span>*/}
                                            </div>
                                            <div className="row align-items-center tutor-mb-30">
                                                <div className="col">
                                                    <div className="tutor-form-check">
                                                        <input id="login-agmnt-1" type="checkbox" className="tutor-form-check-input" name="login-agmnt-1" />
                                                        <label htmlFor="login-agmnt-1">Keep me signed in</label>
                                                    </div>
                                                </div>
                                                <div className="col-auto">
                                                    <a href="#">Forgot?</a>
                                                </div>
                                            </div>
                                            <button type="submit" className="tutor-btn is-primary tutor-is-block">Sign In</button>
                                            <div className="tutor-text-center tutor-mt-15">Don’t have an account? <a href="#">Registration Now</a></div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <br/>
                <br/>

                <div className="row">
                    <div className="col">
                        <div data-tutor-modal-target="modal-03" className="tutor-btn">Open modal</div>
                        <div id="modal-03" className="tutor-modal">
                            <span className="tutor-modal-overlay"></span>
                            <button data-tutor-modal-close className="tutor-modal-close">
                                <span className="las la-times"></span>
                            </button>
                            <div className="tutor-modal-root">
                                <div className="tutor-modal-inner">
                                    <div className="tutor-modal-header">
                                        <h3 className="tutor-modal-title">Add Lesson</h3>
                                    </div>
                                    <div className="tutor-modal-body-alt">
                                        <div className="tutor-mb-30">
                                            <label className="tutor-form-label">Lesson Name</label>
                                            <div className="tutor-input-group tutor-mb-15">
                                                <input type="text" className="tutor-form-control tutor-mb-10" placeholder="Installing linux on local machine"/>
                                                <p className="tutor-input-feedback tutor-has-icon">
                                                    <i className="far fa-question-circle tutor-input-feedback-icon"></i> Topic titles are displayed publicly wherever required. Each topic may contain one or more lessons, quiz and assignments.
                                                </p>
                                            </div>
                                        </div>
                                        <div className="tutor-mb-30">
                                            <label className="tutor-form-label">Lesson Summary</label>
                                            <div className="tutor-input-group tutor-mb-15">
                                                <textarea className="tutor-form-control tutor-mb-10" placeholder="Lesson Summary"></textarea>
                                                <p className="tutor-input-feedback tutor-has-icon">
                                                    <i className="far fa-question-circle tutor-input-feedback-icon"></i> The idea of a summary is a short text to prepare students for the activities within the topic or week. The text is shown on the course page under the topic name.
                                                </p>
                                            </div>
                                        </div>

                                        <div>
                                            <div className="tutor-form-check tutor-mb-15">
                                                <input id="course_privew_check" type="checkbox" className="tutor-form-check-input" name="course_privew_check" />
                                                <label htmlFor="course_privew_check">Enable Course Preview</label>
                                            </div>
                                            <p className="tutor-input-feedback tutor-has-icon">
                                                <i className="far fa-question-circle tutor-input-feedback-icon"></i> If checked, any users/guest can view this lesson without enroll course
                                            </p>
                                        </div>

                                    </div>
                                    <div className="tutor-modal-footer">
                                        <div className="row">
                                            <div className="col">
                                                <button className="tutor-btn tutor-is-primary">Update Lesson</button>
                                            </div>
                                            <div className="col-auto">
                                                <button data-tutor-modal-close className="tutor-btn tutor-is-default">Cancel</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>


            // todo

            <h4>Modal Step</h4>

            <div className="modal-showcase" style={{ background: "#222222", padding: 50}}>
                <div className="row">
                    <div className="col">

                        <div data-tutor-modal-target="modal-02" className="tutor-btn">Open modal</div>

                        <div id="modal-02" className="tutor-modal">
                            <span className="tutor-modal-overlay"></span>
                            <button data-tutor-modal-close className="tutor-modal-close">
                                <span className="las la-times"></span>
                            </button>
                            <div className="tutor-modal-root">
                                <div className="tutor-modal-inner">
                                    <div className="tutor-modal-header">
                                        <h3 className="tutor-modal-title">Add Quiz</h3>
                                    </div>

                                    <div className="tutor-modal-steps">
                                        <ul>
                                            <li className="tutor-is-completed">
                                                <span>Quiz Info</span>
                                                <button className="tutor-modal-step-btn">1</button>
                                            </li>
                                            <li className="tutor-is-completed">
                                                <span>Question</span>
                                                <button className="tutor-modal-step-btn">2</button>
                                            </li>
                                            <li className="tutor-is-completed">
                                                <span>Opt</span>
                                                <button className="tutor-modal-step-btn">3</button>
                                            </li>
                                            <li>
                                                <span>Settings</span>
                                                <button className="tutor-modal-step-btn">4</button>
                                            </li>
                                        </ul>
                                    </div>

                                    <div className="tutor-modal-body-alt">
                                        <div className="tutor-mb-30">
                                            <label className="tutor-form-label">Lesson Name</label>
                                            <div className="tutor-input-group tutor-mb-15">
                                                <input type="text" className="tutor-form-control tutor-mb-10" placeholder="Installing linux on local machine"/>
                                                <p className="tutor-input-feedback tutor-has-icon">
                                                    <i className="far fa-question-circle tutor-input-feedback-icon"></i> Topic titles are displayed publicly wherever required. Each topic may contain one or more lessons, quiz and assignments.
                                                </p>
                                            </div>
                                        </div>
                                        <div className="tutor-mb-30">
                                            <label className="tutor-form-label">Lesson Summary</label>
                                            <div className="tutor-input-group tutor-mb-15">
                                                <textarea className="tutor-form-control tutor-mb-10" placeholder="Lesson Summary"></textarea>
                                                <p className="tutor-input-feedback tutor-has-icon">
                                                    <i className="far fa-question-circle tutor-input-feedback-icon"></i> The idea of a summary is a short text to prepare students for the activities within the topic or week. The text is shown on the course page under the topic name.
                                                </p>
                                            </div>
                                        </div>

                                        <div>
                                            <div className="tutor-form-check tutor-mb-15">
                                                <input id="course_privew_check2" type="checkbox" className="tutor-form-check-input" name="course_privew_check2" />
                                                <label htmlFor="course_privew_check2">Enable Course Preview</label>
                                            </div>
                                            <p className="tutor-input-feedback tutor-has-icon">
                                                <i className="far fa-question-circle tutor-input-feedback-icon"></i> If checked, any users/guest can view this lesson without enroll course
                                            </p>
                                        </div>

                                    </div>
                                    <div className="tutor-modal-footer">
                                        <div className="row">
                                            <div className="col">
                                                <div className="tutor-btn-group">
                                                    <button className="tutor-btn tutor-is-default">Back</button>
                                                    <button className="tutor-btn tutor-is-primary">Save & Next</button>
                                                </div>
                                            </div>
                                            <div className="col-auto">
                                                <button data-tutor-modal-close className="tutor-btn tutor-is-default">Cancel</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            // todo

            <h4>Modal Tab</h4>

            <div className="modal-showcase" style={{ background: "#222222", padding: 50}}>
                <div className="row">
                    <div className="col">

                        <div data-tutor-modal-target="modal-01" className="tutor-btn">Open modal</div>

                        <div id="modal-01" className="tutor-modal">
                            <span className="tutor-modal-overlay"></span>
                            <button data-tutor-modal-close className="tutor-modal-close">
                                <span className="las la-times"></span>
                            </button>
                            <div className="tutor-modal-root">
                                <div className="tutor-modal-inner">
                                    <div className="tutor-modal-header">
                                        <h3 className="tutor-modal-title">Add Quiz</h3>
                                    </div>

                                    <ul className="tutor-modal-tabs">
                                        <li>
                                            <a className="tutor-modal-tab-btn" href="#a">
                                                <span className="tutor-check-icon"></span>
                                                Quiz Info
                                            </a>
                                        </li>
                                        <li>
                                            <a className="tutor-modal-tab-btn tutor-is-active" href="#b">
                                                <span className="tutor-check-icon"></span>
                                                Question
                                            </a>
                                        </li>
                                        <li>
                                            <a className="tutor-modal-tab-btn" href="#c">
                                                <span className="tutor-check-icon"></span>
                                                Settings
                                            </a>
                                        </li>
                                        <li>
                                            <a className="tutor-modal-tab-btn" href="#d">
                                                <span className="tutor-check-icon"></span>
                                                Advance Option
                                            </a>
                                        </li>
                                    </ul>

                                    <div className="tutor-modal-body-alt">
                                        <div className="tutor-mb-30">
                                            <label className="tutor-form-label">Lesson Name</label>
                                            <div className="tutor-input-group tutor-mb-15">
                                                <input type="text" className="tutor-form-control tutor-mb-10" placeholder="Installing linux on local machine"/>
                                                <p className="tutor-input-feedback tutor-has-icon">
                                                    <i className="far fa-question-circle tutor-input-feedback-icon"></i> Topic titles are displayed publicly wherever required. Each topic may contain one or more lessons, quiz and assignments.
                                                </p>
                                            </div>
                                        </div>
                                        <div className="tutor-mb-30">
                                            <label className="tutor-form-label">Lesson Summary</label>
                                            <div className="tutor-input-group tutor-mb-15">
                                                <textarea className="tutor-form-control tutor-mb-10" placeholder="Lesson Summary"></textarea>
                                                <p className="tutor-input-feedback tutor-has-icon">
                                                    <i className="far fa-question-circle tutor-input-feedback-icon"></i> The idea of a summary is a short text to prepare students for the activities within the topic or week. The text is shown on the course page under the topic name.
                                                </p>
                                            </div>
                                        </div>

                                        <div>
                                            <div className="tutor-form-check tutor-mb-15">
                                                <input id="course_privew_check3" type="checkbox" className="tutor-form-check-input" name="course_privew_check3" />
                                                <label htmlFor="course_privew_check3">Enable Course Preview</label>
                                            </div>
                                            <p className="tutor-input-feedback tutor-has-icon">
                                                <i className="far fa-question-circle tutor-input-feedback-icon"></i> If checked, any users/guest can view this lesson without enroll course
                                            </p>
                                        </div>

                                    </div>
                                    <div className="tutor-modal-footer">
                                        <div className="row">
                                            <div className="col">
                                                <div className="tutor-btn-group">
                                                    <button className="tutor-btn tutor-is-default">Back</button>
                                                    <button className="tutor-btn tutor-is-primary">Save & Next</button>
                                                </div>
                                            </div>
                                            <div className="col-auto">
                                                <button data-tutor-modal-close className="tutor-btn tutor-is-default">Cancel</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            // todo
        </div>
    )
}