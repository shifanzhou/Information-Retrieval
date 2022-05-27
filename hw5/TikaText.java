import org.apache.tika.exception.TikaException;
import org.apache.tika.metadata.Metadata;
import org.apache.tika.parser.ParseContext;
import org.apache.tika.parser.html.HtmlParser;
import org.apache.tika.sax.BodyContentHandler;
import org.xml.sax.SAXException;

import java.io.*;
import java.util.*;

public class TikaText {
    public static void main(String[] args) throws IOException, SAXException, TikaException{
        PrintWriter out = new PrintWriter("big.txt");
        File dir = new File("/Users/shifanzhou/Desktop/CSCI572_HW4/foxnews");
        for (File file: Objects.requireNonNull(dir.listFiles())){
            BodyContentHandler handler = new BodyContentHandler(-1);
            Metadata metadata = new Metadata();
            FileInputStream inputStream = new FileInputStream(file);
            ParseContext parseContext = new ParseContext();
            HtmlParser htmlParser = new HtmlParser();
            htmlParser.parse(inputStream,handler,metadata,parseContext);
            out.println(handler.toString());
            String[] metadataNames = metadata.names();
            out.println(Arrays.toString(metadataNames));
        }
        out.close();
        out.flush();
    }
}
