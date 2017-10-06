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
            progress: 0
        };

        this.resumable.on('fileAdded', function(file){

            // Remove navigation
            //$('h1, #sidebar, .bottomhelp').remove();
            //$('#main').removeClass('withsidebar');
            // Handle sync
            //$('#sync').hide();
            //$this.resumable.opts.query['alias_sites'] = $('#alias_sites').val();
            // Add the file
            //$this.addFile(file);
            // We want to upload when files are added
            //$this.progressContainer.show();

            self.setState({progress: 0});
            self.resumable.upload();

        });
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

        console.log(droppable);
    }

    componentWillUnmount() {

    }

    render() {
        return (<div className = "chunk-upload-holder" >
            <button type="button" className="js-browse-btn" ref="browsebutton">Browser file</button>
            <div className="js-droppable chunk-droppable" ref="droppable"></div>
            {this.state.progress > 0 &&
                <div className="chunk-progress" style={{ width: `${ this.state.progress }%`}}></div>
            }
            </div>);
    }

}


(function($) {

   // $(window).on('load', function(){
        var holders = $('.js-chunk-upload');

        holders.each(function(){
            let dom = this;
            let $dom = $(dom);
            let targetURL = $dom.data('target');
            ReactDOM.render(<ChunkUploader targetURL={targetURL}></ChunkUploader>, dom);
        });

   // });


})(jQuery);