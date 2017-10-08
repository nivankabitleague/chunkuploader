import Resumable from '@azmenak/resumablejs';
import React from 'react';
import ReactDOM from 'react-dom';

class ChunkUploader extends React.Component {

    constructor(props) {
        super(props);
        const self = this;
        this.resumable = new Resumable({
            target : this.props.targetURL,
            chunkNumberParameterName: 'flowChunkNumber',
            totalChunksParameterName : 'flowTotalChunks',
            chunkSizeParameterName : 'flowChunkSize',
            totalSizeParameterName : 'flowTotalSize',
            identifierParameterName : 'flowIdentifier',
            fileNameParameterName : 'flowFilename',
            relativePathParameterName : 'flowRelativePath',
            currentChunkSizeParameterName: 'flowCurrentChunkSize'
        });

        this.state = {
            progress: 0,
            error   : '',
            files   : []
        };

        this.resumable.on('fileAdded', function(file){
            self.setState({progress: 0});
            self.resumable.upload();
        });

        this.populateFileInput();
    }

    componentDidMount() {
        const droppable = this.refs.droppable;
        const browsebutton = this.refs.browsebutton;
        const self = this;
        this.resumable.assignBrowse(browsebutton);
        this.resumable.assignDrop(droppable);

        this.resumable.on('fileProgress', function(file){
            let progress = file.progress();
            let percentage = Math.floor(Math.floor(progress*100.0))
            self.setState({progress: percentage});
        });

        this.resumable.on('fileError', function(file, message){
            self.setState({
                progress: 0,
                error   : message
            });
        });


        this.resumable.on('fileSuccess', function(file, message){
            let json = JSON.parse(message);
            let files = self.state.files;
            files.push(json);

            self.setState({
                progress  : 0,
                error     : '',
                files     : files
            });
            self.populateFileInput();
        });
    }

    componentWillUnmount() {

    }

    populateFileInput() {
        let field = jQuery('input[type="hidden"][name="' + this.props.fieldName +  '"]]');
        let ids = [];
        let files = this.state.files;
        files.map(function(file, index) {
            ids.push(file.ID);
        });
        field.val(ids.join(','));
    }

    deleteFile(fileID, deleteLink) {
        const self = this;
        jQuery.ajax(deleteLink).then(function(){
            let files = self.state.files;
            let newFiles = [];
            files.map(function(file, index) {
               if(file.ID != fileID)
                   newFiles.push(file);
            });

            self.setState({
                progress  : 0,
                error     : '',
                files     : newFiles
            });
            self.populateFileInput();
        });

        return false;
    }

    render() {
        const self = this;
        return (<div className = "chunk-upload-holder" >
                <div className="chunk-upload-field">
                    <button type="button" className="js-browse-btn" ref="browsebutton">Browse file</button>
                    <div className="js-droppable chunk-droppable" ref="droppable"></div>
                    {this.state.progress > 0 &&
                        <div className="chunk-progress" style={{ width: `${ this.state.progress }%`}}></div>
                    }
                </div>
                {this.state.error != '' &&
                    <div className="chunk-errors" dangerouslySetInnerHTML={{__html: this.state.error}}></div>
                }
                {this.state.files.length > 0 &&
                    <div className="chunk-filelist">
                        <ul>
                            {this.state.files.map(function(file, index){
                                return <li key={ index }>
                                    <a href={file.Link} target="_blank">{file.FileName}</a>
                                    <a
                                        onClick={(e) => self.deleteFile(file.ID, file.DeleteLink)}
                                        className="delete">Delete</a>
                                </li>;
                            })}
                        </ul>
                    </div>
                }

            </div>);
    }

}


(function($) {

    var holders = $('.js-chunk-upload');

    holders.each(function(){
        let dom = this;
        let $dom = $(dom);
        let targetURL = $dom.data('target');
        let fieldName = $dom.data('name');
        ReactDOM.render(<ChunkUploader targetURL={targetURL} fieldName={fieldName}></ChunkUploader>, dom);
    });


})(jQuery);